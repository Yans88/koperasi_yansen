<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Auth;

class PinjamanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {

        $user_login = auth()->user();

        $type_login = $user_login->type;
        $per_page = (int)$request->per_page > 0 ? (int)$request->per_page : 0;
        $keyword = !empty($request->keyword) ? strtolower($request->keyword) : '';
        $sort_column = !empty($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = !empty($request->sort_order) ? $request->sort_order : 'DESC';
        $page_number = (int)$request->page_number > 0 ? (int)$request->page_number : 1;
        $status = (int)$request->status > 0 ? (int)$request->status : 0;
		
		$from = !empty($request->start_date) ? $request->start_date : '';
        $to = !empty($request->end_date) ? $request->end_date : $from;
		$from = empty($from) && !empty($to) ? $to : $from;	
		
		if(!empty($from) && !empty($to)){
			$from = date('Y-m-d', strtotime($from));
			$to = date('Y-m-d', strtotime($to));
		}
		
        $where = array();
        if ($status > 0) $where += array('status' => $status);
        if ($type_login == 2) $where += array('id_agent' => $user_login->id);
        if ($type_login == 3) $where += array('id_user' => $user_login->id);
        $count = 0;
        $_data = array();
        $data = null;
        if (!empty($keyword)) {
            $_data = DB::table('pinjaman')->select('pinjaman.*','users.name','users.phone')->where($where)
                ->whereRaw("(LOWER(name) like '%" . $keyword . "%' or LOWER(no_pinjaman) like '%" . $keyword . "%')")
				->leftJoin('users', 'users.id', '=', 'pinjaman.id_user');
			if(!empty($from) && !empty($to)){					
				$_data = $_data->whereDate('pinjaman.created_at', '>=', $from)->whereDate('pinjaman.created_at', '<=', $to);
			}			
			$_data = $_data->get();
            $count = count($_data);
        } else {
            $count = DB::table('pinjaman')->where($where)->count();
            $per_page = $per_page > 0 ? $per_page : $count;
            $offset = ($page_number - 1) * $per_page;
            $_data = DB::table('pinjaman')->select('pinjaman.*','users.name','users.phone')->where($where)->leftJoin('users', 'users.id', '=', 'pinjaman.id_user')
                ->offset($offset)->limit($per_page)->orderBy($sort_column, $sort_order);
			if(!empty($from) && !empty($to)){					
				$_data = $_data->whereDate('pinjaman.created_at', '>=', $from)->whereDate('pinjaman.created_at', '<=', $to);
			}			
			$_data = $_data->get();	
        }
        $result = array(
            'err_code'      => '04',
            'err_msg'       => 'data not found',
            'total_data'    => $count,
            'data'          => null
        );
        if ($count > 0) {
			$where = array('users.deleted_at' => null, 'type'=>2);
			$dt_user = DB::table('users')->select('id','name')->where($where)->get();
			$appr_rej_by_name = array();
			foreach ($dt_user as $du) {                 
                $appr_rej_by_name[$du->id] = $du->name;
            }
            foreach ($_data as $d) {        
                $d->appr_rej_by_name = isset($appr_rej_by_name[$d->appr_rej_by]) && (int)$d->appr_rej_by > 0 ? $appr_rej_by_name[$d->appr_rej_by] : '';
                $data[] = $d;
            }
            $result = array(
                'err_code'      => '00',
                'err_msg'          => 'ok',
                'total_data'    => $count,
                'data'          => $data
            );
        }
        return response($result);
    }


	function submit_pinjaman(Request $request){
		$tgl = date('Y-m-d H:i:s');
		$user_login = auth()->user();
		$id_user = $user_login->id;
		$id_agen = $user_login->id_agen;
		$lama_angsuran = (int)$request->lama_angsuran > 0 ? (int)$request->lama_angsuran : 0;
		$suku_bunga = (int)$request->suku_bunga > 0 ? (int)$request->suku_bunga : 0;
		$biaya_adm = (int)$request->biaya_adm > 0 ? $request->biaya_adm : 0;
		$nominal = (int)$request->nominal > 0 ? $request->nominal : 0;
		$ttl_bunga = $lama_angsuran * $suku_bunga;		
		$total = $nominal + ($nominal * $ttl_bunga/100) + $biaya_adm;		
		$dt = array(
			'id_user'		=> $id_user,
			'id_agent'		=> $id_agen,
			'nominal'		=> $nominal,
			'suku_bunga'	=> $suku_bunga,
			'nominal'		=> $nominal,
			'nominal_bunga'	=> $nominal * $ttl_bunga/100,
			'biaya_adm'		=> $biaya_adm,
			'total'			=> $total,
			'angsuran'		=> ceil($total / $lama_angsuran),
			'lama_angsuran'	=> $lama_angsuran,
			'sisa_nyicil'	=> $lama_angsuran,
			'sisa_angsuran'	=> $total,
			'status'		=> 1,
			'created_at'	=> $tgl,
		);
		$id = DB::table('pinjaman')->insertGetId($dt, "id");
		$result = array(
            'err_code'  => '05',
            'err_msg'   => 'Insert has problem',
            'data'      => null
        );
		if($id){
			$no_pinjaman = 'YNS-'.date('YmdHi').'-'.$id;
			DB::table('pinjaman')->where('id', $id)->update(array('no_pinjaman' => $no_pinjaman));
			$dt += array('no_pinjaman' => $no_pinjaman, 'id'=>$id);
			$result = array(
                'err_code'      => '00',
                'err_msg'          => 'ok',              
                'data'          => $dt
            );
		}
		return response($result);
	}
	
	function upd_status_pinjaman(Request $request){
		$tgl = date('Y-m-d H:i:s');
		$user_login = auth()->user();
		$type_login = $user_login->type;
		$result = array(
			'err_code'      => '05',
			'err_msg'       => 'no akses type_login : '.$type_login,              
			'data'          => ''
		);
		if($type_login == 2){
			$id = (int)$request->id > 0 ? (int)$request->id : 0;
			$status = (int)$request->status > 0 ? (int)$request->status : 0;
			$id_user = $user_login->id;
			$dt = array('status' => $status,'appr_rej_by'=>$id_user,'appr_rej_date'=>$tgl);
			DB::table('pinjaman')->where('id', $id)->update($dt);
			$result = array(
				'err_code'      => '00',
				'err_msg'       => 'ok',              
				'data'          => $dt
			);
		}		
		return response($result);
	}
    function submit_cicilan(Request $request){
		$tgl = date('Y-m-d H:i:s');
		$user_login = auth()->user();
		$id_user = $user_login->id;	
		$type_login = $user_login->type;
		$result = array(
			'err_code'      => '05',
			'err_msg'       => 'no akses type_login : '.$type_login,              
			'data'          => ''
		);		
		if($type_login == 2){
			$id = (int)$request->id > 0 ? (int)$request->id : 0;
			$dt_cicilan = DB::table('cicilan')->select('angsuran_ke')->where(array('id_pinjaman'=>$id))->get()->last();
			$_data = DB::table('pinjaman')->select('angsuran','sisa_nyicil')->where(array('id'=>$id))->first();
			$result = array(
				'err_code'      => '06',
				'err_msg'       => 'Pinjaman sudah lunas',              
				'data'          => ''
			);
			$sisa_nyicil = $_data->sisa_nyicil;
			if((int)$sisa_nyicil > 0){
				$angsuran = $_data->angsuran;
				$sisa_nyicil = (int)$sisa_nyicil - 1;
				$sisa_angsuran = $sisa_nyicil * $angsuran;
				$dt_insert = array(
					'id_pinjaman'	=> $id,
					'received_by'	=> $id_user,
					'created_at'	=> $tgl,
					'angsuran_ke'	=> isset($dt_cicilan) ? (int)$dt_cicilan->angsuran_ke + 1 : 1,
				);
				$dt_upd = array(
					'id'			=> $id,
					'sisa_nyicil'	=> $sisa_nyicil,
					'sisa_angsuran'	=> $sisa_angsuran > 0 ? $sisa_angsuran : 0,			
				);
				if($sisa_nyicil == 0){
					$dt_upd += array('status' => 3);
				}
				DB::table('cicilan')->insert($dt_insert);
				DB::table('pinjaman')->where('id', $id)->update($dt_upd);
				$dt_insert += array(
					'angsuran'		=> $angsuran,
					'sisa_nyicil'	=> $sisa_nyicil,
					'sisa_angsuran'	=> $sisa_angsuran > 0 ? $sisa_angsuran : 0
				);
				$result = array(
					'err_code'      => '00',
					'err_msg'       => 'ok',              
					'data'          => $dt_insert
				);
			}
		}
		return response($result);
	}
	
	function detail(Request $request){
		$id = (int)$request->id > 0 ? (int)$request->id : 0;
		$where = array('pinjaman.id' => $id);
		$_data = DB::table('pinjaman')->select('pinjaman.*','users.name','users.phone')->where($where)
				->leftJoin('users', 'users.id', '=', 'pinjaman.id_user')->first();
		$dt_user = DB::table('users')->select('name')->where(array('id'=>$_data->id_agent))->first();
		$dt_cicilan = DB::table('cicilan')->select('name as received_name','angsuran_ke','received_by','cicilan.created_at')->where(array('id_pinjaman'=>$_data->id))->leftJoin('users', 'users.id', '=', 'cicilan.received_by')->get();
		$_data->appr_rej_by_name = isset($dt_user) ? $dt_user->name : '';
		$_data->history_cicilan = $dt_cicilan;
		$result = array(
            'err_code'      => '00',
            'err_msg'          => 'ok',              
            'data'          => $_data
        );
		return response($result);
	}
}