<?php

namespace App\Http\Controllers\Backend;

use App\Models\Stock;
use App\Models\StockBook;
use App\Models\StockPlace;

use App\User;
use App\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Datatables;
use File;

class StockController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
	{
		$stock_place = StockPlace::all();

		return view('backend.stock.index')->with(compact('request', 'stock_place'));
	}

	public function datatables(Request $request)
	{
		$sql_stock_book = "(SELECT * FROM `stock_books` WHERE status = \"BORROWING\") AS stock_books";

		$index = Stock::leftJoin(DB::raw($sql_stock_book), 'stock_books.stock_id', 'stocks.id')
			->leftJoin('stock_places', 'stocks.stock_places_id', 'stock_places.id')
			->select('stocks.*', DB::raw('COALESCE(SUM(stock_books.quantity_borrow - stock_books.quantity_return), 0) AS quantity_borrow'), 'stock_places.name as place')
			->groupBy('stocks.id')
			->orderBy('id', 'DESC');
		$index = $index->get();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
			$html = '';

			if (Auth::user()->can('edit-stock')) {
				$html .= '
					<a class="btn btn-xs btn-warning" href="' . route('backend.stock.edit', ['id' => $index->id]) . '"><i class="fa fa-edit"></i></a>
				';
			}

			if (Auth::user()->can('delete-stock')) {
				$html .= '
					<button class="btn btn-xs btn-danger delete-stock" data-toggle="modal" data-target="#delete-stock" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
				';
			}

			return $html;
		});

		$datatables->addColumn('check', function ($index) {
			$html = '';
			$html .= '
				<input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
			';
			return $html;
		});

		$datatables->editColumn('photo', function ($index) {
			$html = '';
			if($index->photo)
			{
				$html = '<a href="'.asset($index->photo).'" target="_new"><img src="'.asset($index->photo).'" style="width:2em;height:2em;object-fit: contain;"></img></a>';
			}
			
			return $html;
		});

		$datatables->editColumn('created_at', function ($index) {
			$html = date('d/m/Y H:i', strtotime($index->created_at));

			return $html;
		});

		$datatables->editColumn('updated_at', function ($index) {
			$html = date('d/m/Y H:i', strtotime($index->updated_at));

			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function create()
	{
		$stock_place = StockPlace::all();

		return view('backend.stock.create', compact('stock_place'));
	}

	public function store(Request $request)
	{

		$message = [
			'item.required'     => 'This field required.',
			'quantity.required' => 'This field required.',
			'quantity.integer'  => 'Number Only.',
			'stock_places_id.required' => 'This field required.',
			'stock_places_id.integer'  => 'Number Only.',
			'photo.image'  => 'Image Only.',
		];

		$validator = Validator::make($request->all(), [
			'item'     => 'required',
			'quantity' => 'required|integer',
			'stock_places_id' => 'required|integer',
			'photo' => 'nullable|image',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('create-stock-error', 'Error');
		}

		DB::transaction(function () use ($request) {
			$index = new Stock;

			$index->item     = $request->item;
			$index->quantity = $request->quantity;
			$index->stock_places_id = $request->stock_places_id;

			if ($request->hasFile('photo')) {
                $pathSource = 'upload/stock/';
                $file       = $request->file('photo');
                $filename   = time();

                $file->move($pathSource, $filename);
                $index->photo = $pathSource . $filename;
            }

			$index->save();
		});

		return redirect()->route('backend.stock')->with('success', 'Data Has Been Added');
	}

	public function edit($id)
	{
		$index = Stock::find($id);
		$stock_place = StockPlace::all();

		return view('backend.stock.edit', compact('index', 'stock_place'));
	}

	public function update(Request $request, $id)
	{
		$index = Stock::find($id);

		$quantity_borrow = Stock::join('stock_books', 'stock_books.stock_id', 'stocks.id')->select(DB::raw('SUM(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'))
			->where('stock_books.status', 'BORROWING')
			->where('stocks.id', $index->id)
			->first()->quantity_borrow;

		$message = [
			'item.required'     => 'This field required.',
			'quantity.required' => 'This field required.',
			'quantity.integer'  => 'Number Only.',
			'stock_places_id.required' => 'This field required.',
			'stock_places_id.integer'  => 'Number Only.',
			'photo.image'  => 'Image Only.',
		];

		$validator = Validator::make($request->all(), [
			'item'     => 'required',
			'quantity' => 'required|integer',
			'stock_places_id' => 'required|integer',
			'photo' => 'nullable|image',
		], $message);

		$validator->after(function ($validator) use ($request, $quantity_borrow) {
			if ($request->quantity < $quantity_borrow) {
				$validator->errors()->add('quantity', 'Quantity can\'t below quantity borrowed');
			}
		});

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('edit-stock-error', 'Error');
		}

		DB::transaction(function () use ($request, $index) {
			$this->saveArchive('App\Models\Stock', 'UPDATED', $index);

			$index->item     = $request->item;
			$index->quantity = $request->quantity;
			$index->stock_places_id = $request->stock_places_id;

			if ($request->hasFile('photo')) {
                if ($index->photo) {
                    File::delete($index->photo);
                }
                $pathSource = 'upload/stock/';
                $fileData   = $request->file('photo');
                $filename   = time() . '.' . $fileData->getClientOriginalExtension();
                $fileData->move($pathSource, $filename);

                $index->photo = $pathSource . $filename;
            } else if (isset($request->remove_photo)) {
                File::delete($index->photo);
                $index->photo = null;
            }

			$index->save();
		});

		return redirect()->route('backend.stock')->with('success', 'Data Has Been Updated');
	}

	public function delete(Request $request)
	{
		$index = Stock::find($request->id);

		$quantity_borrow = Stock::join('stock_books', 'stock_books.stock_id', 'stocks.id')->select(DB::raw('SUM(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'))
			->where('stock_books.status', 'BORROWING')
			->where('stocks.id', $index->id)
			->first()->quantity_borrow;

		if($quantity_borrow > 0)
		{
			return redirect()->back()->with('failed', 'Data can\'t be Deleted');
		}

		$this->saveArchive('App\Models\Stock', 'DELETED', $index);

		Stock::destroy($request->id);

		return redirect()->back()->with('success', 'Data Has Been Deleted');
	}

	public function action(Request $request)
	{
		if (is_array($request->id)) {
			foreach ($request->id as $list) {

				$quantity_borrow = Stock::join('stock_books', 'stock_books.stock_id', 'stocks.id')->select(DB::raw('SUM(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'))
					->where('stock_books.status', 'BORROWING')
					->where('stocks.id', $list)
					->first()->quantity_borrow;

				if ($quantity_borrow == 0) {
					$id[] = $list;
				}
			}

			if ($request->action == 'delete' && Auth::user()->can('delete-stock')) {

				DB::transaction(function () use ($request){
					$index = Stock::find($id);
					$this->saveMultipleArchive('App\Models\Stock', 'DELETED', $id);

					Stock::destroy($id);
				});

				return redirect()->back()->with('success', 'Data Has Been Deleted');
			}
			return redirect()->back()->with('failed', 'Access Denied');
		}

		return redirect()->back()->with('failed', 'No Data Selected');
	}

	public function stockBook(Request $request)
	{
		$status = ["BORROWING" => "Dipinjam", "RETURNED" => "Dikembalikan"];
		return view('backend.stock.stockBook.index')->with(compact('request', 'status'));
	}

	public function datatablesStockBook(Request $request)
	{
		$f_stock_id = $this->filter($request->f_stock_id);
		$f_status   = $this->filter($request->f_status);

		$index = StockBook::join('stocks', 'stock_books.stock_id', 'stocks.id')->select('stock_books.*', 'stocks.item', DB::raw('(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'))->orderBy('stock_books.id', 'DESC');

		if ($f_stock_id != '')
		{
			$index->where('stock_books.stock_id', $f_stock_id);
		}

		if ($f_status != '')
		{
			$index->where('stock_books.status', $f_status);
		}

		$index = $index->get();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
			$html = '';

			if (Auth::user()->can('editStockBook-stock')) {
				$html .= '
					<a class="btn btn-xs btn-warning" href="' . route('backend.stock.editStockBook', ['id' => $index->id]) . '"><i class="fa fa-edit"></i></a>
				';
			}

			if (Auth::user()->can('deleteStockBook-stock')) {
				$html .= '
					<button class="btn btn-xs btn-danger delete-stock" data-toggle="modal" data-target="#delete-stock" data-id="' . $index->id . '"><i class="fa fa-trash"></i></button>
				';
			}

			if (Auth::user()->can('statusStockBook-stock')) {
					$html .= '
					   <button type="button" class="btn btn-xs btn-success return-stock" data-toggle="modal" data-target="#return-stock"
						   data-id="' . $index->id . '"
					   ><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
					';
					$html .= '
					   <button type="button" class="btn btn-xs btn-dark borrow-stock" data-toggle="modal" data-target="#borrow-stock"
						   data-id="' . $index->id . '"
					   ><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
					';
			}

			return $html;
		});

		$datatables->editColumn('need', function ($index) {

			$need = ["OFFICE" => "Kantor", "EXPO" => "Pameran"];

			return ($index->need == '' ? '' : $need[$index->need]);
		});

		$datatables->addColumn('check', function ($index) {
			$html = '';
			$html .= '
				<input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
			';
			return $html;
		});

		$datatables->editColumn('date_borrow', function ($index) {
			$html = date('d/m/Y', strtotime($index->date_borrow));

			return $html;
		});

		$datatables->editColumn('deadline_borrow', function ($index) {
			$html = date('d/m/Y', strtotime($index->deadline_borrow));

			return $html;
		});

		$datatables->editColumn('status', function ($index) {
			$html = '';
			if ($index->status == "BORROWING") {
				$html .= '
					<span class="label label-info">Dipinjam</span>
				';
			} else {
				$html .= '
					<span class="label label-success">Dikembalikan ' .date('d/m/Y H:i', strtotime($index->returned_at)). '</span>
				';
			}
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function createStockBook()
	{
		$stock = Stock::orderBy('item', 'ASC')->get();

		$need = ["OFFICE" => "Kantor", "EXPO" => "Pameran"];

		return view('backend.stock.stockBook.create', compact('stock', 'need'));
	}

	public function storeStockBook(Request $request)
	{
		$message = [
			'stock_id.required'    => 'This field required.',

			'name_borrow.required' => 'This field required.',
			'need.required' => 'This field required.',

			'date_borrow.required' => 'This field required.',
			'date_borrow.date'     => 'Date Format Only.',

			'deadline_borrow.required' => 'This field required.',
			'deadline_borrow.date'     => 'Date Format Only.',

			'quantity_borrow.required' => 'This field required.',
			'quantity_borrow.integer'  => 'Number Only.',

		];

		$validator = Validator::make($request->all(), [
			'stock_id'        => 'required',
			'name_borrow'     => 'required',
			'need'     => 'required',
			'date_borrow'     => 'required|date',
			'deadline_borrow' => 'required|date',
			'quantity_borrow' => 'required|integer',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('create-stock-error', 'Error');
		}

		$quantity_stock = Stock::join('stock_books', 'stock_books.stock_id', 'stocks.id')->select(DB::raw('SUM(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'), 'stocks.quantity')
			->where('stock_books.status', 'BORROWING')
			->where('stocks.id', $request->stock_id)
			->first();

		$validator->after(function ($validator) use ($request, $quantity_stock) {
			if ($request->quantity_borrow > $quantity_stock->quantity - $quantity_stock->quantity_borrow) {
				$validator->errors()->add('quantity_borrow', 'Out of Quantity');
			}
		});

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('create-stock-error', 'Error');
		}

		DB::transaction(function () use ($request) {
			$index = new StockBook;

			$index->stock_id        = $request->stock_id;
			$index->name_borrow     = $request->name_borrow;
			$index->need            = $request->need;
			$index->date_borrow     = $request->date_borrow;
			$index->deadline_borrow = $request->deadline_borrow;
			$index->quantity_borrow = $request->quantity_borrow;
			$index->note            = $request->note;

			$index->save();
		});

		return redirect()->route('backend.stock.stockBook')->with('success', 'Data Has Been Added');
	}

	public function editStockBook($id)
	{
		$stock = Stock::orderBy('item', 'ASC')->get();

		$index = StockBook::find($id);

		$need = ["OFFICE" => "Kantor", "EXPO" => "Pameran"];

		return view('backend.stock.stockBook.edit', compact('index', 'stock', 'need'));
	}

	public function updateStockBook(Request $request, $id)
	{
		$index = StockBook::find($id);

		$message = [
			'stock_id.required'    => 'This field required.',

			'name_borrow.required' => 'This field required.',
			'need.required' => 'This field required.',

			'date_borrow.required' => 'This field required.',
			'date_borrow.date'     => 'Date Format Only.',

			'deadline_borrow.required' => 'This field required.',
			'deadline_borrow.date'     => 'Date Format Only.',

			'quantity_borrow.required' => 'This field required.',
			'quantity_borrow.integer'  => 'Number Only.',

		];

		$validator = Validator::make($request->all(), [
			'stock_id'        => 'required',
			'name_borrow'     => 'required',
			'need'     => 'required',
			'date_borrow'     => 'required|date',
			'deadline_borrow' => 'required|date',
			'quantity_borrow' => 'required|integer',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('create-stock-error', 'Error');
		}

		$quantity_stock = Stock::join('stock_books', 'stock_books.stock_id', 'stocks.id')->select(DB::raw('SUM(stock_books.quantity_borrow - stock_books.quantity_return) AS quantity_borrow'), 'stocks.quantity')
			->where('stock_books.status', 'BORROWING')
			->where('stocks.id', $request->stock_id)
			->where('stock_books.id', '<>', $id)
			->first();

		$validator->after(function ($validator) use ($request, $quantity_stock) {
			if ($request->quantity_borrow > $quantity_stock->quantity - $quantity_stock->quantity_borrow) {
				$validator->errors()->add('quantity_borrow', 'Out of Quantity');
			}
		});

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput()->with('create-stock-error', 'Error');
		}

		DB::transaction(function () use ($request, $index) {
			$this->saveArchive('App\Models\StockBook', 'UPDATED', $index);

			$index->stock_id        = $request->stock_id;
			$index->name_borrow     = $request->name_borrow;
			$index->need            = $request->need;
			$index->date_borrow     = $request->date_borrow;
			$index->deadline_borrow = $request->deadline_borrow;
			$index->quantity_borrow = $request->quantity_borrow;
			$index->note            = $request->note;

			$index->save();

			$index->save();
		});

		return redirect()->route('backend.stock.stockBook')->with('success', 'Data Has Been Updated');
	}

	public function deleteStockBook(Request $request)
	{
		$index = StockBook::find($request->id);
		$this->saveArchive('App\Models\StockBook', 'DELETED', $index);

		StockBook::destroy($request->id);

		return redirect()->back()->with('success', 'Data Has Been Deleted');
	}

	public function actionStockBook(Request $request)
	{
		if (is_array($request->id)) {
			if ($request->action == 'delete' && Auth::user()->can('delete-stock')) {

				DB::transaction(function () use ($request){
					$index = StockBook::find($id);
					$this->saveMultipleArchive('App\Models\StockBook', 'DELETED', $index);

					StockBook::destroy($id);
				});

				return redirect()->back()->with('success', 'Data Has Been Deleted');
			} else if ($request->action == 'returned' && Auth::user()->can('status-stock')) {

				DB::transaction(function () use ($request){
					$index = StockBook::whereIn('id', $id);
					$this->saveMultipleArchive('App\Models\StockBook', 'STATUS', $index);

					StockBook::whereIn('id', $id)->update(['status' => 'RETURNED', 'returned_at' => date('Y-m-d H:i:s')]);
				});

				return redirect()->back()->with('success', 'Data Has Been Updated');
			} else if ($request->action == 'borrowing' && Auth::user()->can('status-stock')) {

				DB::transaction(function () use ($request){
					$index = StockBook::whereIn('id', $id);
					$this->saveMultipleArchive('App\Models\StockBook', 'STATUS', $index);

					StockBook::whereIn('id', $id)->update(['status' => 'BORROWING', 'returned_at' => null]);
				});

				return redirect()->back()->with('success', 'Data Has Been Updated');
			}

			return redirect()->back()->with('failed', 'Access Denied');
		}

		return redirect()->back()->with('failed', 'No Data Selected');
	}

	public function statusStockBook(Request $request)
	{
		$index = StockBook::find($request->id);

		$sql_stock_book = "(SELECT * FROM `stock_books` WHERE status = \"BORROWING\") AS stock_books";

		$quantity_stock = Stock::leftJoin(DB::raw($sql_stock_book), 'stock_books.stock_id', 'stocks.id')
			->select('stocks.*', DB::raw('COALESCE(SUM(stock_books.quantity_borrow - stock_books.quantity_return), 0) AS quantity_borrow'))
			->groupBy('stocks.id')
			->where('stocks.id', $index->stock_id)
			->first();
		
		DB::transaction(function () use ($request, $index, $quantity_stock){
			$message = [
				'quantity_return.required' => 'This field required.',
				'quantity_return.min'      => 'Minimum 1.',
				'quantity_return.max'      => 'Out Quantity.',
				'quantity_return.integer'  => 'Number Only.',
				'returned_at.required_if' => 'This field required.',
				'returned_at.date'        => 'Date Format Only.',
			];

			if($request->status == "RETURNED")
			{
				$validator = Validator::make($request->all(), [
					'quantity_return' => 'required|integer|min:1|max:'.($index->quantity_borrow - $index->quantity_return),
					'returned_at'     => 'required_if:status,RETURNED|date',
				], $message);

				if ($validator->fails()) {
					return redirect()->back()->withErrors($validator)->withInput()->with('return-stock-error', 'Error');
				}
			}
			else
			{
				$validator = Validator::make($request->all(), [
					'quantity_return' => 'required|integer|min:1|max:'.($quantity_stock->quantity - $quantity_stock->quantity_borrow),
					'returned_at'     => 'required_if:status,RETURNED|date',
				], $message);

				if ($validator->fails()) {
					return redirect()->back()->withErrors($validator)->withInput()->with('borrow-stock-error', 'Error');
				}
			}

			$this->saveArchive('App\Models\StockBook', 'STATUS', $index);

			
			if($request->status == "RETURNED")
			{
				$index->quantity_return += $request->quantity_return;
				if (($index->quantity_borrow - $index->quantity_return) == 0)
				{
					$index->status ="RETURNED";
					$index->returned_at = $request->returned_at;
				}
				
			}
			else
			{
				$index->quantity_return -= $request->quantity_return;
				$index->status = "BORROWING";
				$index->returned_at = null;
			}
			
			
			$index->save();
		});

		return redirect()->back()->with('success', 'Data Has Been Updated');
	}

	public function stockPlace(Request $request)
	{
		return view('backend.stock.place', compact('request'));
	}

	public function datatablesStockPlace(Request $request)
	{
		$index = StockPlace::all();

		$datatables = Datatables::of($index);

		$datatables->addColumn('action', function ($index) {
            $html = '';
            
            if (Auth::user()->can('editStockPlace-stock')) {
                $html .= '
                   <button type="button" class="btn btn-xs btn-warning editStockPlace-stock" data-toggle="modal" data-target="#editStockPlace-stock" data-id="' . $index->id . '" data-name="' . $index->name . '"><i class="fa fa-edit" aria-hidden="true"></i></button>
                ';
            }

            if (Auth::user()->can('deleteStockPlace-stock')) {
                $html .= '
                    <button type="button" class="btn btn-xs btn-danger deleteStockPlace-stock" data-toggle="modal" data-target="#deleteStockPlace-stock" data-id="' . $index->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                ';
            }

            return $html;
        });

        $datatables->addColumn('check', function ($index) {
            $html = '';
            $html .= '
                <input type="checkbox" class="check" value="' . $index->id . '" name="id[]" form="action">
            ';
            return $html;
        });

        $datatables = $datatables->make(true);
        return $datatables;
	}

	public function storeStockPlace(Request $request)
	{
		$message = [
            'name.required'         => 'This field required.',
        ];

        $validator = Validator::make($request->all(), [
			'name' => 'required',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('createStockPlace-stock-error', 'Error')->withInput();
		}

		$index = new StockPlace;

		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Added'); 
	}

	public function updateStockPlace(Request $request)
	{
		$index = StockPlace::find($request->id);

		$message = [
            'name.unique'           => 'This name already exist.',
        ];

        $validator = Validator::make($request->all(), [
			'name' => 'required',
		], $message);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('editStockPlace-stock-error', 'Error')->withInput();
		}

		$this->saveArchive('App\Models\StockPlace', 'UPDATED', $index);

		$index->name = $request->name;

		$index->save();

		return redirect()->back()->with('success', 'Data Has Been Updated'); 
	}

	public function deleteStockPlace(Request $request)
	{
		$index = StockPlace::find($request->id);

		$this->saveArchive('App\Models\StockPlace', 'DELETED', $index);

		$index->delete();

		return redirect()->back()->with('success', 'Data Has Been Deleted'); 
	}
}
