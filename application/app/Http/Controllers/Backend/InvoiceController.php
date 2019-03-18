<?php

namespace App\Http\Controllers\Backend;

use App\Models\Spk;
use App\Models\Invoice;
use App\User;
use App\Config;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Session;
use File;
use Hash;
use Validator;
use PDF;
use Excel;

use Yajra\Datatables\Facades\Datatables;

use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}
	
	public function index(Request $request)
	{
		$year = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();

		$month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

		$sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
			->select('sales.first_name', 'sales.last_name', 'sales.id')
			->orderBy('sales.first_name', 'ASC')->distinct()->get();


		return view('backend.invoice.index')->with(compact('year', 'month', 'sales', 'request'));
	}

	public function datatables(Request $request)
	{
		$f_admin    = $this->filter($request->f_admin);
		$f_done     = $this->filter($request->f_done);
		$f_complete = $this->filter($request->f_complete);
		$f_inv      = $this->filter($request->f_inv);
		$f_received = $this->filter($request->f_received);
		$f_send     = $this->filter($request->f_send);
		$f_check    = $this->filter($request->f_check);
		$f_sales    = $this->filter($request->f_sales);
		$f_year     = $this->filter($request->f_year, date('Y'));
		$f_month    = $this->filter($request->f_month, date('n'));
		$search     = $this->filter($request->search);

		$index = Spk::withStatisticProduction()
			->withStatisticInvoice()
			->withStatisticPr()
			->join('users as sales', 'sales.id', '=', 'spk.sales_id')
			->join('invoices', 'invoices.spk_id', '=', 'spk.id')
			->distinct()
			->select('spk.id', 'spk.no_spk', 'spk.name', 'spk.sales_id', 'spk.code_admin', 'spk.finish_spk_at',	'spk.note_invoice',	'spk.check_master',
				'total_hm',	'total_he',	'total_hj',	'total_ppn', 'count_production', 'count_production_finish',	'datetime_finish',
				'sum_value_invoice', 'count_invoice', 'count_invoice_complete', 'sum_value_pr'
			);

		if($search != '')
		{
			$index->where(function ($query) use ($search) {
                $query->where('spk.no_spk', 'like', '%'.$search.'%')
                    ->orWhere('invoices.no_invoice', 'like', '%'.$search.'%');
            });
		}
		else
		{
			if ($f_admin != '')
			{
				$index->where('spk.code_admin', $f_admin);
			}

			if ($f_done != '' && $f_done == 'UNFINISH_PROD') {
                $index->whereNull('finish_spk_at')->where(function ($query) {
                    $query->whereColumn('sum_quantity_production', '>', 'sum_quantity_production_finish')->orWhere('count_production', 0);
                });
            } else if ($f_done == 'UNFINISH_SPK') {
                $index->whereNull('finish_spk_at')->whereColumn('sum_quantity_production', '<=', 'sum_quantity_production_finish');
            } else if ($f_done == 'FINISH') {
                $index->whereNotNull('finish_spk_at');
            }

			if ($f_complete != '' && $f_complete == 0)
			{
				$index->whereNull('invoices.datetime_add_complete');
			}
			else if ($f_complete == 1)
			{
				$index->whereNotNull('invoices.datetime_add_complete');
			}

			if ($f_inv != '' && $f_inv == 0)
			{
				$index->whereNull('invoices.value_invoice');
			}
			else if ($f_inv == 1)
			{
				$index->whereNotNull('invoices.value_invoice');
			}

			if ($f_received != '' && $f_received == 0)
			{
				$index->whereNull('invoices.date_received');
			}
			else if ($f_received == 1)
			{
				$index->whereNotNull('invoices.date_received');
			}

			if ($f_send != '' && $f_send == 0)
			{
				$index->whereNull('invoices.datetime_add_sending');
			}
			else if ($f_send == 1)
			{
				$index->whereNotNull('invoices.datetime_add_sending');
			}

			if ($f_check != '')
			{
				$index->where('spk.check_master', $f_check);
			}

			if($f_month != '')
			{
				$index->whereMonth('spk.date_spk', $f_month);
			}

			if($f_year != '')
			{
				$index->whereYear('spk.date_spk', $f_year);
			}

			if($f_sales != '')
			{
				$index->where('spk.sales_id', $f_sales);
			}
		}

		$index = $index->get();

		$datatables = Datatables::of($index);

		$datatables->editColumn('no_spk', function ($index) {
			$html = '<b>No SPK</b> : '.$index->no_spk.'<br/>';
			$html .= '<b>Name</b> : '.$index->name.'<br/>';
			$html .= '<b>Finish Project</b> : '.($index->finish_spk_at ? date('d/m/Y', strtotime($index->finish_spk_at)) : 'Not_Finish').'<br/>';
			$html .= '<b>Sales</b> : '.($index->sales->fullname ?: '-').'<br/>';
			$html .= '<b>HM</b> : Rp. '.number_format($index->total_hm).'<br/>';
			$html .= '<b>HE</b> : Rp. '.number_format($index->total_he).'<br/>';
			$html .= '<b>HJ</b> : Rp. '.number_format($index->total_hj).'<br/>';
			$html .= '<b>PPN</b> : Rp. '.number_format($index->total_ppn).'<br/>';
			$html .= '<b>Production Count</b> : '.$index->count_production.'<br/>';
			$html .= '<b>Production Complete</b> : '.$index->count_production_finish.'<br/>';
			if($index->count_production == $index->count_production_finish && $index->count_production > 0)
			{
				$html .= '<b>Production Finish</b> : '.date('d-m-Y', strtotime($index->datetime_finish)).'<br/>';
			}
			
			return $html;
		});

		$datatables->editColumn('sum_value_invoice', function ($index) {
			$html = '<b>Total Invoice</b> : Rp. '.number_format($index->sum_value_invoice).'<br/>';

			$html .= '<b>No Admin</b> : ';

			if(Auth::user()->can('admin-invoice', $index))
			{
				$html .= '
					<select class="form-control" name="code_admin" data-id="'.$index->id.'" style="height: 1.8em; font-size: 13px; padding: 0;">
						<option value="0" '. ($index->code_admin === 0 ? 'selected' : '') .'>0</option>
						<option value="-1" '. ($index->code_admin === -1 ? 'selected' : '') .'>KB</option>
						<option value="-2" '. ($index->code_admin === -2 ? 'selected' : '') .'>BK</option>
						<option value="-3" '. ($index->code_admin === -3 ? 'selected' : '') .'>KT</option>';

				for ($i=1; $i <= getConfigValue('num_admin') ; $i++) { 
					$html .= '<option value="'.$i.'" '. ($index->code_admin == $i ? 'selected' : '') .'>No '.$i.'</option>';
				}

				$html .= '
					</select>
				';
			}
			else
			{
				$html .= $index->code_admin;
			}


			return $html;
		});

		$datatables->addColumn('data_invoice', function ($index) {
			return view('backend.invoice.datatables.data_invoice', compact('index'));
		});
		
		$datatables->editColumn('note_invoice', function ($index) {
			$html = '';

			// Class noteInvoice
			if(Auth::user()->can('admin-invoice', $index))
			{
				$html .= '
					<textarea class="note_invoice form-control" data-id="' . $index->id . '" name="note_invoice">'.$index->note_invoice.'</textarea>
				';
			}
			else
			{
				$html .= '
					'.$index->note_invoice.'
				';
			} 
			return $html;
		});
		
		$datatables->editColumn('check_master', function ($index) {
			$html = '';

			// Class checkMaster
			if(Auth::user()->can('checkMaster-invoice'))
			{
				$html .= '
					<input type="checkbox" data-id="' . $index->id . '" value="1" name="check_master" '.($index->check_master ? 'checked' : '').'>
				';
			}
			else if($index->check_master)
			{
				$html .=
				'
					<i class="fa fa-check" aria-hidden="true"></i>
				';
			}
		   
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function getStatus(Request $request)
	{
		$f_admin    = $this->filter($request->f_admin);
		$f_done     = $this->filter($request->f_done);
		$f_complete = $this->filter($request->f_complete);
		$f_inv      = $this->filter($request->f_inv);
		$f_received = $this->filter($request->f_received);
		$f_send     = $this->filter($request->f_send);
		$f_check    = $this->filter($request->f_check);
		$f_sales    = $this->filter($request->f_sales);
		$f_year     = $this->filter($request->f_year, date('Y'));
		$f_month    = $this->filter($request->f_month, date('n'));
		$search     = $this->filter($request->search);

		$index = Spk::withStatisticProduction()
			->withStatisticInvoice()
			->withStatisticPr()
			->join('users as sales', 'sales.id', '=', 'spk.sales_id')
			->join('invoices', 'invoices.spk_id', '=', 'spk.id')
			->distinct()
			->select('spk.id', 'spk.no_spk', 'spk.name', 'spk.sales_id', 'spk.code_admin', 'spk.finish_spk_at',	'spk.note_invoice',	'spk.check_master',
				'total_hm',	'total_he',	'total_hj',	'total_ppn', 'count_production', 'count_production_finish',	'datetime_finish',
				'sum_value_invoice', 'count_invoice', 'count_invoice_complete', 'sum_value_pr'
			);

		if($search != '')
		{
			$index->where(function ($query) use ($search) {
                $query->where('spk.no_spk', 'like', '%'.$search.'%')
                    ->orWhere('invoices.no_invoice', 'like', '%'.$search.'%');
            });
		}
		else
		{
			if ($f_admin != '')
			{
				$index->where('spk.code_admin', $f_admin);
			}

			if ($f_done != '' && $f_done == 'UNFINISH_PROD') {
                $index->whereNull('finish_spk_at')->where(function ($query) {
                    $query->whereColumn('sum_quantity_production', '>', 'sum_quantity_production_finish')->orWhere('count_production', 0);
                });
            } else if ($f_done == 'UNFINISH_SPK') {
                $index->whereNull('finish_spk_at')->whereColumn('sum_quantity_production', '<=', 'sum_quantity_production_finish');
            } else if ($f_done == 'FINISH') {
                $index->whereNotNull('finish_spk_at');
            }

			if ($f_complete != '' && $f_complete == 0)
			{
				$index->whereNull('invoices.datetime_add_complete');
			}
			else if ($f_complete == 1)
			{
				$index->whereNotNull('invoices.datetime_add_complete');
			}

			if ($f_inv != '' && $f_inv == 0)
			{
				$index->whereNull('invoices.value_invoice');
			}
			else if ($f_inv == 1)
			{
				$index->whereNotNull('invoices.value_invoice');
			}

			if ($f_received != '' && $f_received == 0)
			{
				$index->whereNull('invoices.date_received');
			}
			else if ($f_received == 1)
			{
				$index->whereNotNull('invoices.date_received');
			}

			if ($f_send != '' && $f_send == 0)
			{
				$index->whereNull('invoices.datetime_add_sending');
			}
			else if ($f_send == 1)
			{
				$index->whereNotNull('invoices.datetime_add_sending');
			}

			if ($f_check != '')
			{
				$index->where('spk.check_master', $f_check);
			}

			if($f_month != '')
			{
				$index->whereMonth('spk.date_spk', $f_month);
			}

			if($f_year != '')
			{
				$index->whereYear('spk.date_spk', $f_year);
			}

			if($f_sales != '')
			{
				$index->where('spk.sales_id', $f_sales);
			}
		}

		$index = $index->get();

		$count = $total_hj = $total_hm = $sum_value_invoice = $sum_value_pr = $amends = 0;

		foreach ($index as $list) {
			$count++;
			$total_hm += $list->total_hm;
			$total_hj += $list->total_hj;
			$sum_value_invoice += $list->sum_value_invoice;
			$sum_value_pr += $list->sum_value_pr;
			$amends += $list->total_hj - $list->sum_value_invoice;
		}

		return compact('count', 'total_hm', 'total_hj', 'sum_value_invoice', 'sum_value_pr', 'amends');

	}

	public function dashboard(Request $request)
	{
		$year = Spk::select(DB::raw('YEAR(date_spk) as year'))->orderBy('date_spk', 'ASC')->distinct()->get();

		$month = ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

		$sales = Spk::join('users as sales', 'sales.id', '=', 'spk.sales_id')
			->select('sales.first_name', 'sales.last_name', 'sales.id')
			->orderBy('sales.first_name', 'ASC')->distinct();

		if(!Auth::user()->can('full-user'))
		{
			$sales->whereIn('sales_id', Auth::user()->staff());
		}

		$sales = $sales->get();

		return view('backend.invoice.dashboard')->with(compact('year', 'month', 'sales', 'request'));
	}

	public function datatablesDashboard(Request $request)
	{
		$f_sales    = $this->filter($request->f_sales, Auth::id());
		$f_year     = $this->filter($request->f_year, date('Y'));
		$f_month    = $this->filter($request->f_month, date('n'));


		$f_start_year   = $this->filter($request->f_start_year, date('Y'));
        $f_start_month  = $this->filter($request->f_start_month, date('n'));
        $f_end_year     = $this->filter($request->f_end_year, date('Y'));
        $f_end_month    = $this->filter($request->f_end_month, date('n'));
        $f_type         = $this->filter($request->f_type);

        $start_range = $f_start_year.'-'.sprintf("%02d",$f_start_month).'-01';
        $end_range   = $f_end_year.'-'.sprintf("%02d",$f_end_month).'-'. date('t', strtotime($f_end_year.'-'.sprintf("%02d",$f_end_month).'-01'));
		
		
		$index = Spk::withStatisticProduction()
			->withStatisticInvoice()
			->select(
				'spk.sales_id',
				DB::raw('COUNT(spk.id) as count_spk'),
				DB::raw('SUM(total_hm) as total_hm'),
				DB::raw('SUM(total_hj) as total_hj'),
				DB::raw('SUM(sum_value_invoice) as sum_value_invoice'),
				DB::raw('(SUM(total_hj) - COALESCE(SUM(sum_value_invoice), 0)) as amends')
			)
			->groupBy('spk.sales_id');

		if($f_type == 'range')
        {
            $index->whereBetween('spk.date_spk', [$start_range, $end_range]);
        }
        else
        {
            if($f_year)
			{
				$index->whereYear('spk.date_spk', $f_year);
			}

			if($f_month)
			{
				$index->whereMonth('spk.date_spk', $f_month);
			}
        }

		if($f_sales == 'staff')
		{
			$index->whereIn('spk.sales_id', Auth::user()->staff());
		}
		else if($f_sales != '')
		{
			$index->where('spk.sales_id', $f_sales);
		}

		$index = $index->get();

		$datatables = Datatables::of($index);

		$datatables->addColumn('fullname', function ($index) {
			$html = $index->sales->fullname;

			return $html;
		});

		$datatables->editColumn('count_spk', function ($index) {
			$html = number_format($index->count_spk);

			return $html;
		});

		$datatables->editColumn('total_hm', function ($index) {
			$html = 'Rp.'. number_format($index->total_hm);

			return $html;
		});

		$datatables->editColumn('total_hj', function ($index) {
			$html = 'Rp.'. number_format($index->total_hj);

			return $html;
		});

		$datatables->editColumn('sum_value_invoice', function ($index) {
			$html = 'Rp.'. number_format($index->sum_value_invoice);

			return $html;
		});

		$datatables->editColumn('amends', function ($index) {
			$html = 'Rp.'. number_format($index->amends);

			return $html;
		});		

		$datatables = $datatables->make(true);


		return $datatables;
	}

	public function datatablesDetailDashboard(Request $request)
	{
		$f_year     = $this->filter($request->f_year, date('Y'));
		$f_month    = $this->filter($request->f_month, date('n'));

		$f_start_year   = $this->filter($request->f_start_year, date('Y'));
        $f_start_month  = $this->filter($request->f_start_month, date('n'));
        $f_end_year     = $this->filter($request->f_end_year, date('Y'));
        $f_end_month    = $this->filter($request->f_end_month, date('n'));
        $f_type         = $this->filter($request->f_type);

        $start_range = $f_start_year.'-'.sprintf("%02d",$f_start_month).'-01';
        $end_range   = $f_end_year.'-'.sprintf("%02d",$f_end_month).'-'. date('t', strtotime($f_end_year.'-'.sprintf("%02d",$f_end_month).'-01'));

		$index = Spk::withStatisticInvoice()->withStatisticProduction()
			->where('spk.sales_id', $request->sales_id);

		if($f_type == 'range')
        {
            $index->whereBetween('spk.date_spk', [$start_range, $end_range]);
        }
        else
        {
            if($f_year)
			{
				$index->whereYear('spk.date_spk', $f_year);
			}

			if($f_month)
			{
				$index->whereMonth('spk.date_spk', $f_month);
			}
        }


		$index = $index->get();

		$datatables = Datatables::of($index);


		$datatables->editColumn('total_hj', function ($index) {
			return 'Rp. '. number_format($index->hj_production);
		});

		$datatables->editColumn('value_invoice', function ($index) {
			return 'Rp. '. number_format($index->sum_value_invoice);
		});

		$datatables->addColumn('amends', function ($index) {
			return 'Rp. '. number_format($index->hj_production - $index->sum_value_invoice);
		});

		$datatables->addColumn('data_invoice', function ($index) {
			$html = '';

			$html .= '<table class="table table-striped">';

			$html .= '
				<tr>
					<th nowrap="nowrap">No Invoice</th>
					<th nowrap="nowrap">Value Invoice</th>
				</tr>
			';

			foreach($index->invoices as $list)
			{
				$html .= '<tr>';


				if($list->no_invoice)
				{
					$html .=
					'
						<td nowrap="nowrap">
							'.$list->no_invoice.'
						</td>
						<td nowrap="nowrap">
							Rp. '.number_format($list->value_invoice).'
						</td>
					';
				}

				$html .= '</tr>';
			}
			$html .= '</table>';

			return $html;
		});

		$datatables->addColumn('year_month', function ($index) use ($f_month, $f_year) {
			return $f_month . ' ' . $f_year;
		});

		$datatables->editColumn('note_invoice', function ($index) {
			$html = '';
			// Class noteInvoice
			if(Auth::user()->can('create-invoice', $index))
			{
				$html .= '
					<textarea class="note_invoice form-control" data-id="' . $index->id . '" name="note_invoice">'.$index->note_invoice.'</textarea>
				';
			}
			else
			{
				$html .= '
					'.$index->note_invoice.'
				';
			}
			return $html;
		});

		$datatables = $datatables->make(true);
		return $datatables;
	}

	public function noAdmin(Request $request)
	{
		$index = Spk::find($request->id);

		if(!Auth::user()->can('admin-invoice', $index))
		{
			return 'Data cannot to update';
		}

		saveArchives($index, Auth::id(), 'change admin invoice', $request->except('_token'));

		$index->code_admin = $request->code_admin;

		$index->save();

		saveArchives($index, Auth::id(), 'change admin invoice', $request->except('_token'));
	}

	public function addDocument(Request $request)
	{
		$index = new Invoice;

		$spk = Spk::find($request->id);

		if(!Auth::user()->can('create-invoice', $spk))
		{
			return redirect()->back()->with('failed', 'Data cannot to add');
		}

		saveArchives($index, Auth::id(), 'add document invoice', $request->except('_token'));

		$index->spk_id = $request->id;
		$index->datetime_add_complete = date('Y-m-d H:i:s');

		$index->save();

		return redirect()->back()->with('success', 'Data has been added');
	}

	public function redoDocument(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('update-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'redo invoice', $request->except('_token'));

		$index->datetime_add_complete = date('Y-m-d H:i:s');

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function undoDocument(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('undo-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'undo invoice', $request->except('_token'));

		$index->datetime_add_complete = null;

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function delete(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('delete-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to add');
		}

		saveArchives($index, Auth::id(), 'delete invoice', $request->except('_token'));

		Invoice::destroy($request->id);

		return redirect()->back()->with('success', 'Data has been deleted');
	}

	public function addInvoice(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('update-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		$validator = Validator::make($request->all(), [
			'no_invoice' => 'required',
			'value_invoice' => 'required|numeric',
			'date_faktur' => 'required|date',
		]);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('update-invoice-error', '')->withInput();
		}

		saveArchives($index, Auth::id(), 'add invoice', $request->except('_token'));

		$index->no_invoice    = $request->no_invoice;
		$index->value_invoice = $request->value_invoice;
		$index->date_faktur   = date('Y-m-d', strtotime($request->date_faktur));

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function undoInvoice(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('update-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'undo invoice', $request->except('_token'));

		$index->no_invoice    = null;
		$index->value_invoice = null;
		$index->date_faktur   = null;

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function addReceived(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('update-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'received invoice', $request->except('_token'));

		$index->date_received = date('Y-m-d h:i:s');

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function undoReceived(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('undo-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'undo received invoice', $request->except('_token'));

		$index->date_received = null;

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function addSend(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('update-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}


		$validator = Validator::make($request->all(), [
			'no_sending' => 'required',
		]);

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->with('add-send-error', '')->withInput();
		}

		saveArchives($index, Auth::id(), 'send invoice', $request->except('_token'));

		$index->no_sending = $request->no_sending;
		if($index->datetime_add_sending == NULL)
		{
			$index->datetime_add_sending = date('Y-m-d h:i:s');
		}
		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function undoSend(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('undo-invoice', $index))
		{
			return redirect()->back()->with('failed', 'Data cannot to update');
		}

		saveArchives($index, Auth::id(), 'undo send invoice', $request->except('_token'));

		$index->no_sending           = null;
		$index->datetime_add_sending = null;

		$index->save();

		return redirect()->back()->with('success', 'Data has been updated');
	}

	public function checkFinance(Request $request)
	{
		$index = Invoice::find($request->id);

		if(!Auth::user()->can('checkFinance-invoice', $index))
		{
			return 'Data cannot to update';
		}

		saveArchives($index, Auth::id(), 'check Finance invoice', $request->except('_token'));

		$index->check_finance = $request->check_finance;

		$index->save();
	}

	public function noteInvoice(Request $request)
	{
		$index = spk::find($request->id);

		if(!Auth::user()->can('admin-invoice', $index))
		{
			return 'Data cannot to update';
		}

		saveArchives($index, Auth::id(), 'note invoice', $request->except('_token'));

		$index->note_invoice = $request->note_invoice;

		$index->save();
	}

	public function checkMaster(Request $request)
	{
		$index = spk::find($request->id);

		saveArchives($index, Auth::id(), 'check master invoice', $request->except('_token'));

		$index->check_master = $request->check_master;

		$index->save();
	}

	public function excel(Request $request)
	{
		$f_year  = $this->filter($request->xls_year, date('Y'));
		$f_month = $this->filter($request->xls_month, date('n'));

		Excel::create('invoice-'.$f_year.$f_month.'-'.date('dmYHis'), function ($excel) use ($f_year, $f_month) {
			$excel->sheet('List', function ($sheet) use ($f_year, $f_month) {

				$index = Spk::withStatisticProduction()
					->join('invoices', 'invoices.spk_id', 'spk.id');


				if($f_month != '')
				{
					$index->whereMonth('spk.date_spk', $f_month);
				}

				if($f_year != '')
				{
					$index->whereYear('spk.date_spk', $f_year);
				}

				if(!Auth::user()->can('full-user'))
				{
					$index->whereIn('spk.sales_id', Auth::user()->staff());
				}

				$index = $index->get();

				$data = '';
				foreach ($index as $list) {
					if($list->count_production == $list->count_production_finish && $list->count_production > 0)
					{
						$date_finish_production = date('d/m/Y', strtotime($list->date_done));
					}
					else
					{
						$date_finish_production = '';
					}

					$data[] = [
						$list->NO_spk,
						$list->sales->fullname,
						$list->name,

						$list->finish_spk_at ?? '',
						$list->count_production_finish.' of '.$list->count_production,
						$date_finish_production,

						$list->total_hm,
						$list->total_hj,

						$list->total_ppn ?? 0,
						$list->code_admin,
						$list->no_invoice,

						$list->datetime_add_complete,
						$list->value_invoice,
						$list->date_faktur,

						$list->date_received,
						$list->no_sending,
						$list->datetime_add_sending,

						$list->check_finance ? 'Yes' : 'No',
						$list->check_master ? 'Yes' : 'No',
					];
				}

				$sheet->fromArray($data);
				$sheet->row(1, [
					'SPK',
					'Sales',
					'Project',

					'Date Finish Project',
					'Status Production',
					'Date Finish Production',

					'Modal Price',
					'Sell Price',

					'PPn',
					'No Admin',
					'No Invoice',

					'Date Complete',
					'Value Invoice',
					'Date Faktur',

					'Date Complete Invoice',
					'No Send',
					'Date Sended',

					'Check Finance',
					'Check Master'
				]);
				$sheet->setFreeze('A1');
			});

			$excel->sheet('Dashboard', function ($sheet) use ($f_year, $f_month) {

				$where_spk = [];

				if($f_month != '')
				{
					$where_spk[] = 'MONTH(spk.date_spk) = '. $f_month;
				}

				if($f_year != '')
				{
					$where_spk[] = 'YEAR(spk.date_spk) = '. $f_year;
				}

				$index = User::withStatisticSpk(implode(' AND ', $where_spk))->where('count_spk', '>', 0);

				if(!Auth::user()->can('full-user'))
				{
					$index->whereIn('users.id', Auth::user()->staff());
				}


				$index = $index->get();

				$data = '';
				foreach ($index as $list) {
					$data[] = [
						$list->first_name . ' ' . $list->last_name,
						$list->total_hm,
						$list->total_hj,

						$list->sum_value_invoice,
						$list->total_hj - $list->sum_value_invoice,
					];
				}

				$sheet->fromArray($data);
				$sheet->row(1, [
					'Sales',
					'Total Modal Price',
					'Total Sell Price',

					'Total Invoice',
					'Amends Invoice'
				]);
				$sheet->setFreeze('A1');
			});

			$index = Spk::select('spk.sales_id')->distinct();

			if($f_month != '')
			{
				$index->whereMonth('spk.date_spk', $f_month);
			}

			if($f_year != '')
			{
				$index->whereYear('spk.date_spk', $f_year);
			}

			if(!Auth::user()->can('full-user'))
			{
				$index->whereIn('spk.sales_id', Auth::user()->staff());
			}

			$index = $index->get();

			foreach ($index as $list) {

				$sales_id = $list->sales_id;
				$name     = $list->sales->fullname;

				$excel->sheet($name, function ($sheet) use ($f_year, $f_month, $sales_id) {


					$index = Spk::withStatisticProduction()
						->withStatisticInvoice()
						->where('spk.sales_id', $sales_id);

					if($f_month != '')
					{
						$index->whereMonth('date_spk', $f_month);
					}

					if($f_year != '')
					{
						$index->whereYear('date_spk', $f_year);
					}

					$index = $index->get();

					$data = '';
					foreach ($index as $list) {
						$data[] = [
							$list->no_spk,
							$list->name,

							$list->total_hm,
							$list->total_hj,

							$list->total_ppn,

							$list->sum_value_invoice,
						];
					}

					$sheet->fromArray($data);
					$sheet->row(1, [
						'SPK',
						'Project',

						'Modal Price',
						'Sell Price',

						'PPn',

						'Value Invoice',
					]);
					$sheet->setFreeze('A1');
				});
			}
		})->download('xls');
	}

}
