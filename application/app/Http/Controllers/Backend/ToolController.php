<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use File;
use Input;

class ToolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function labelPackage()
    {
    	return view('backend.tool.labelPackage');
    }

    public function downloadLabelPackageTemplate()
    {
    	return Excel::create('Template Label Packages Import', function ($excel) {
            $excel->sheet('Data-Import', function ($sheet) {
                $sheet->row(1, array('nama_toko', 'asal', 'alamat', 'nama_penerima', 'telpon', 'jumlah_part', 'jumlah', 'unit'));
                $sheet->setColumnFormat(array(
                    'A' => '',
                    'B' => '',
                    'C' => '',

                    'D' => '',
                    'E' => '',
                    'F' => '',

                    'G' => '',
                    'H' => '',
                ));
            });
        })->download('xls');
    }

    public function generateLabelPackage(Request $request)
    {
    	$list = '';

    	$image = '';
    	if ($request->hasFile('image')) {
            $pathSource = 'upload/temp/';
            $file       = $request->file('image');
            $filename   = 'temp' . '.' . $file->getClientOriginalExtension();

    		File::delete($pathSource, $filename);

            if($file->move($pathSource, $filename))
            {
            	$image = $pathSource . $filename;
            }
        }

        $collect = [];
        if ($request->hasFile('import')) {
            $path = $request->file('import')->getRealPath();
            $data = Excel::selectSheets('Data-Import')->load($path, function ($reader) {
            })->get();

            if (!empty($data) && $data->count()) {
                foreach ($data as $key) {
                    $collect[] = [
                        'nama_toko'     => $key->nama_toko,
                        'asal'          => $key->asal,
                        'alamat'        => $key->alamat,
                        'nama_penerima' => $key->nama_penerima,
                        'telpon'        => $key->telpon,
                        'jumlah_part'   => $key->jumlah_part,
                        'jumlah'        => $key->jumlah,
                        'unit'          => $key->unit,
                    ];
                }
            } 
        } 

    	return view('backend.tool.download.labelPackage', compact('request', 'collect', 'image'));
    }
}
