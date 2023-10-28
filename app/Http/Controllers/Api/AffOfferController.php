<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Offer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AffOfferController extends Controller
{
    public function index(Request $request) {
        return "Ok";
    }

    public function offer(Request $request, $aff_id,$key) {
            if(!$aff_id) {
                    return $this->errorResponse("invalid affiliate id!");
                }
            if(!$key) {
                return  $this->errorResponse("invalid API Key!");
                }
        
        $aff = DB::table('affiliates')->where('id',$aff_id)->first();
                if(!$aff) {
                    return  $this->errorResponse("invalid affiliate id!");
                }
                if($aff->secret_token !== $key) {
                    return  $this->errorResponse("invalid API Key!"); 
                }
        $aff_global_access = DB::table('offer_access')->where('offerid',0)->where('affiliateid',$aff_id)->first();

            $OfferAPICachekey = 'OfferApiCache'.$aff_id.$key.$request->ip().$request->page.json_encode($aff_global_access).$request->keyword.$request->offer_access.$request->country.$request->offer_status;
        $offer = Cache::remember($OfferAPICachekey, 1800, function () use($aff_id,$request,$aff_global_access) {
            return  Offer::join('categories','offers.offer_category','categories.category_id')->whereNot('offer_access',4)
            ->where(function($query) use($request,$aff_id) {
                    $query->whereNull('rejected_affiliate')->orWhere('rejected_affiliate','NOT LIKE','%'.$aff_id."%");
                    if($request->offer_access !== null) {
                            $query->where('offer_access',$request->offer_access);
                    } 
                    if($request->keyword !== null) {
                        $q->where('offerid',$request->keyword)->orWhere('offer_name',$request->keyword);
                    }
                    if($request->country !== null) {
                        $q->whereIn('country_allow',[$request->country]);
                    }
                    if($request->offer_access !== null) {
                        if($request->offer_access == 3) {
                            $q->where('assigned_affiliate','like',$aff_id);
                        }
                    }
                    if($request->offer_status !== null) {
                        $q->where('offer_status',$request->offer_status);
                    }
            })->orderBy('offerid','desc')->paginate(2500);
        });

        $offer->map(function($offer) use($aff_id,$aff_global_access) {
                        $offer->can_run = false;
                        $offer->tracking_url = null;
                        $offer->assigned_affiliate = "not assigned";
                if($aff_global_access && $aff_global_access->access == 1) {
                        $offer->can_run = true;
                        $offer->assigned_affiliate = 'assigned';
                        $offer->tracking_url = "https://clicktrk.diginlink.com/click/".$offer->offerid.'/'.$aff_id;
                } else{
                        if($offer->assigned_affiliate !== null) {
                            if(in_array($aff_id,explode(',',$offer->assigned_affiliate))) {
                                $offer->assigned_affiliate = 'assigned';
                                $offer->can_run = true;
                                $offer->tracking_url = "https://clicktrk.diginlink.com/click/".$offer->offerid.'/'.$aff_id;
                        }
                    }
                }
            });
                return response()->json([
                    'request_ip'=>$request->ip(),
                    'aff_id' => $aff_id,
                    'api_key' =>$key,
                    'max_limit'=>2500,
                    'offers'=>$offer
                ],200);
            
    }

    public function transaction(Request $request, $aff_id,$key) {
       
        
                if(!$aff_id) {
                    return $this->errorResponse("invalid affiliate id!");
                }
                if(!$key) {
                    return  $this->errorResponse("invalid API Key!");
                    }

            $aff = DB::table('affiliates')->where('id',$aff_id)->first();
           
            if(!$aff) {
                    return  $this->errorResponse("invalid affiliate id!");
                }
                if($aff->secret_token !== $key) {
                    return  $this->errorResponse("invalid API Key!"); 
                }
               
                    if($request->start_date == null || $request->start_date == "empty") {
                        return  $this->errorResponse("invalid empty start date!"); 
                        }
                    $validator = Validator::make($request->all(),[
                        'start_date'=>'required|date_format:Y-m-d'
                    ]);
                if($validator->fails()) {
                    return $validator->errors();
                }

                $start_date = Carbon::parse($request->start_date)->startOfDay()->format('Y-m-d H:i:s');
                $end_date = Carbon::now()->endOfDay()->format('Y-m-d');

                $report = DB::table('general_reports')->join('offers','general_reports.offerid','=','offers.offerid')
                ->where('affiliateid',$aff_id)
                ->selectRaw(DB::raw("affiliateid,offers.offerid as offerid, offers.offer_name as offer_name, offers.offer_currency as currency, conversionid as DglnkTransactionID, conversion_time, offers.modelOut as payout_model, JSON_UNQUOTE(JSON_EXTRACT(conversion_data,'$.sale')) as sale,JSON_UNQUOTE(JSON_EXTRACT(conversion_data,'$.status')) as status,JSON_UNQUOTE(JSON_EXTRACT(conversion_data,'$.priceOut')) as priceOut, JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub1')) as sub1,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub2')) as sub2,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub3')) as sub3,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub4')) as sub4,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub5')) as sub5,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub6')) as sub6,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub7')) as sub7,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub8')) as sub8,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub9')) as sub9,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.sub10')) as sub10,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.deviceid')) as deviceid,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.androidid')) as androidid,JSON_UNQUOTE(JSON_EXTRACT(click_data,'$.googleaid')) as googleaid"))
                ->where('conv_count',1)
                ->where(function($query) use($start_date,$end_date) {
                      $query->whereBetween('general_reports.created_at',[$start_date,$end_date]);
                      $query->orWhereBetween('general_reports.conversion_time',[$start_date,$end_date]);
                })->paginate(1000);

                return $report;

    }

    public function errorResponse($message) {
            return response()->json([
                'status' => 'error',
                'response'=> $message
            ],401);
    }
 }
