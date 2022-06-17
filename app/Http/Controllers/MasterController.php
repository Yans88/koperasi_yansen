<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MasterController extends Controller
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

    //

    public function index(Request $request)
    {
        $cms = (int)$request->cms > 0 ? (int)$request->cms : 0;
        $setting = DB::table('setting')->get()->toArray();
        $out = array();
        if (!empty($setting)) {
            foreach ($setting as $val) {
                $out[$val->setting_key] = $val->setting_val;
            }
        }
        if ($cms == 0) {
            unset($out['mail_pass']);
            unset($out['send_mail']);           
            unset($out['content_forgotPass']);           
            unset($out['content_forgotPin']);
            unset($out['about_us']);                  
        }
		
		unset($out['content_reg']);
		unset($out['subj_email_register']);
        unset($out['subj_email_forgot']);   
        $result = array(
            'err_code'  => '00',
            'err_msg'   => 'ok',
            'data'      => $out
        );
        $id_member = (int)$request->id_member > 0 ? Helper::last_login((int)$request->id_member) : 0;
        return response($result);
    }

    function upd_setting(Request $request){
		$input  = $request->all();
		foreach($input as $key=>$val){
			$where = array();
			$dt = array();
			$where = array("setting_key"=>"$key");
			$dt = ["setting_val" => "$val"];
			DB::table('setting')->where($where)->update($dt);
		}
		$result = array(
            'err_code'  => '00',
            'err_msg'   => 'ok',
            'data'      => $input
        );
		return response($result);
	}
	
	
	function get_dashboard(){
		$user_login = auth()->user();
		$type_login = $user_login->type;
		$count_agen = 0;
		$count_anggota = 0;
		$pinjaman = 0;
		if($type_login == 1){
			$where = array('users.deleted_at' => null,'users.type'=>3);
			$count_anggota = DB::table('users')->where($where)->count();
			$where = array('users.deleted_at' => null,'users.type'=>2);
			$count_agen = DB::table('users')->where($where)->count();
			$pinjaman = DB::table('pinjaman')
                ->select(DB::raw('SUM(nominal) as nominal'))
                ->first();
			
		}
		
		if($type_login == 2){
			$where = array('users.deleted_at' => null,'users.type'=>3,'id_agen'=>$user_login->id);
			$count_anggota = DB::table('users')->where($where)->count();
			$pinjaman = DB::table('pinjaman')->where(array('id_agent'=>$user_login->id))
                ->select(DB::raw('SUM(nominal) as nominal'))
                ->first();
		}
		
		if($type_login == 3){			
			$pinjaman = DB::table('pinjaman')->where(array('id_user'=>$user_login->id))
                ->select(DB::raw('SUM(nominal) as nominal'))
                ->first();
		}
		
		$dt = array(
			'jml_anggota'	=> $count_anggota,
			'jml_agen'		=> $count_agen,
			'jml_pinjaman'	=> $pinjaman->nominal,
			
		);
		$result = array(
            'err_code'  => '00',
            'err_msg'   => 'ok',
            'data'      => $dt
        );
		return response($result);
	}
	
	function get_dashboard_agen(Request $request){
		$where = array('users.deleted_at' => null,'users.type'=>3);
		$count_anggota = DB::table('users')->where($where)->count();		
		$dt = array(
			'jml_anggota'	=> $count_anggota,			
			'jml_pinjaman'	=> 30000,
		);
		$result = array(
            'err_code'  => '00',
            'err_msg'   => 'ok',
            'data'      => $dt
        );
		return response($result);
	}
	
	function get_dashboard_anggota(Request $request){
		$where = array('users.deleted_at' => null,'users.type'=>3);
		$count_anggota = DB::table('users')->where($where)->count();		
		$dt = array(					
			'cnt_pinjaman'	=> 3,
			'jml_pinjaman'	=> 30000,
			'sisa_pinjaman'	=> 30000,
		);
		$result = array(
            'err_code'  => '00',
            'err_msg'   => 'ok',
            'data'      => $dt
        );
		return response($result);
	}
	
}
