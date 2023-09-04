<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
class Offer extends Model
{
    use HasFactory;

    protected $table = "offers";
    protected $primaryKey = 'offerid';

    protected $casts = [
        'offer_start_date' => 'datetime:Y-m-d H:i:s',
        'offer_end_date' => 'datetime:Y-m-d H:i:s'
    ];
    protected $hidden = [ 'created_at','updated_at','priceIn','modelIn','rev_comm','offer_category','user_id','pending_affiliate','rejected_affiliate','advertiser_id','external_id','api_id','offer_url','deleted_at','payoutRules','offer_kpi','targeting_activation','country_block','state_allow','state_block','city_allow','city_block'];

 
    public function getCountryAllowAttribute($value) {
        return explode(',',$value);
    }

    public function getOfferAccessAttribute($value) {
        if($value == 1) {
            return "Open";
        }
        if($value == 2) {
            return "OnRequest";
        }
        if($value == 3) {
            return "Private";
        }
        if($value == 4) {
            return "Hidden";
        }
     
    }

    public function getOfferStatusAttribute($value) {
        if($value == 1) {
            return "Approve";
        }
        if($value == 2) {
            return "Pending";
        }
        if($value == 3) {
            return "Reject";
        }     
    }

    public function getOfferLogoAttribute($value) {
        if (strpos($value, 'http://') === 0 || strpos($value, 'https://') === 0) {
            return $value; // Return the original URL
        }else {
            return "https://app.diginlink.com/public/storage/".$value;
        }
    }



}
