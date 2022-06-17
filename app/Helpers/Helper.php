<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Helper
{
	
	

   static function last_login($id_member = 0)
    {
        $tgl = date('Y-m-d H:i:s');
        DB::table('members')->where('id_member', $id_member)->update(['last_login' => $tgl]);
        return $id_member;
    }	
	
	static function upd_stok($data = array())	
    {
        for ($i = 0; $i < count($data); $i++) {
            $sku_no = 0;
            $sku_no = $data[$i]['sku_no'];
            $id_ca = $data[$i]['id_ca'];
			$where = array(
				'deleted_at' 	=> null,
				'sku_no'	=> $sku_no,
				'id_ca'		=> $id_ca,
			);
			// DB::connection()->enableQueryLog();
            DB::table('stock')->where($where)->update(['qty' => $data[$i]['qty']]);
			// Log::info(DB::getQueryLog());
        }
        return true;
    }
	
	function save_reward_xp($id_transaksi=0){
		$where = array('transaksi.id_transaksi' => $id_transaksi);
		
        $_data = DB::table('transaksi')->select(
					'transaksi.*',
					'members.nama as nama_member',
					'members.email as email_member',
					'members.phone as phone_member'	
			)->where($where)->leftJoin('members', 'members.id_member', '=', 'transaksi.id_member')->first();
	}
	

    static function send_fcm($id_member=0,$data_fcm=array(),$notif_fcm=array()){
		$url = 'https://fcm.googleapis.com/fcm/send';		
		$server_key = env('FCM_KEY');
		$fields = array();		
		$result = array();		
		$fields['data'] = $data_fcm;
		$fields['notification'] = $notif_fcm;
		$where = array('id_member'=>$id_member);
		$fcm_token = DB::table('fcm_token')->where($where)->get(); 
		$target = array();
		if(!empty($fcm_token)){
			foreach($fcm_token as $dt){
				array_push($target ,$dt->token_fcm);
			}
			$fields['registration_ids'] = $target;
			$headers = array(
				'Content-Type:application/json',
				'Authorization:key='.$server_key
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('FCM Send Error: ' . curl_error($ch));
			}
			curl_close($ch);
		}	
		Log::info("push notif :".$id_member);
		Log::info($result);
		return $result;
		
	}
	
	static function post_data_api($post_data=array(),$method = "POST"){
		$url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';		
		$auth ='Basic U0ItTWlkLXNlcnZlci1QQU1lN1Q3Wi13a3M0aExvcEFYazlLTnc6';
		$fields = array();
		$headers = array(
			"Content-Type:application/json",
			"Accept: application/json",
			"Authorization:$auth"
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));		
		$result = curl_exec($ch);
		if ($result === FALSE) {
			die('Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
		$res = json_decode($result);
		$error_messages = isset($res) && !empty($res->error_messages) ? $res->error_messages[0] : '';		
		$url_payment = isset($res) && empty($error_messages) ? $res->redirect_url : $error_messages;
		return $url_payment;
	}
	
	static function get_status_veritrans($id=0){
		$url = "https://api.sandbox.midtrans.com/v2/$id/status";
		$auth ='Basic U0ItTWlkLXNlcnZlci1QQU1lN1Q3Wi13a3M0aExvcEFYazlLTnc6';
		Log::info($url);
		$headers = array(
			"Content-Type:application/json",
			"Accept: application/json",
			"Authorization:$auth"
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);		
		curl_close($ch);
		if ($result === FALSE) {
	      throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
	    }
	    else {
	      $result_array = json_decode($result);
	      if (!in_array($result_array->status_code, array(200, 201, 202, 407))) {
	        $message = 'Veritrans Error (' . $result_array->status_code . '): '
	            . $result_array->status_message;
	        throw new Exception($message, $result_array->status_code);
	      }
	      else {
	        return $result_array;
	      }
	    }		
	}
	
}
