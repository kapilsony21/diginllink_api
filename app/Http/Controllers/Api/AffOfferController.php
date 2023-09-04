<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Offer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;


class AffOfferController extends Controller
{
    public function index(Request $request) {
        return "Ok";
    }

    public function offer(Request $request, $aff_id,$key) {
            if(!$aff_id) {
                    $this->errorResponse("invalid affiliate id!");
                }
            if(!$key) {
                $this->errorResponse("invalid API Key!");
                }
        
        $aff = DB::table('affiliates')->where('id',$aff_id)->where('secret_token',$key)->first();
        $aff_global_access = DB::table('offer_access')->where('offerid',0)->where('affiliateid',$aff_id)->first();
                Cache::forget('OfferApiCache');
        $offer = Cache::remember('OfferApiCache', 1800, function () use($aff_id) {
            return  Offer::whereNot('rejected_affiliate','LIKE','%'.$aff_id.'%')->join('categories','offers.offer_category','categories.category_id')
            ->whereNot('offer_access',4)->orderBy('offers.created_at','desc')->paginate(500);
        });

        $offer->each(function($offer) use($aff_id,$aff_global_access) {
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
                return $offer;
            
    }

    public function errorResponse($message) {
            return response()->json([
                'status' => 'error',
                'response'=> $message
            ],400);
    }
 }
