<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleMainSetting;
use App\Models\User;
use App\Traits\ResponseTrait;
use App\Traits\UploadFileTrait;
use App\Traits\ValidationRequestTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ResponseTrait;
    use ValidationRequestTrait;
    use UploadFileTrait;

    private $rules;
    private $messageRules;
    private $token_name;

    public function __construct()
    {
        $this->token_name = env('PASSPORT_TOKEN_NAME');
        $this->rules = collect([
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'male_nickname' => 'required',
            'female_nickname' => 'required'
        ]);
        $this->messageRules = [
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'male_nickname.required' => 'Nama Panggilan Pria harus diisi',
            'female_nickname.required' => 'Nama Panggilan Wanita harus diisi',
        ];
    }
    
    /**
     * Register new user
     *
     * @param  mixed $request
     * @param String : email
     * @param String : password
     * @param String : male_nickname
     * @param String : female_nickname
     * @return void
     */
    public function create(Request $request) {
        // begin::validation
        $validation = $this->validateReq($request, $this->rules->toArray(), $this->messageRules);
        if (isset($validation['error'])) {
            return $this->sendFailedValidation($validation['error']);
        }
        // end::validation

        DB::beginTransaction();
        try {
            $email = $request->email;
            $password = $request->password;
            $male_nickname = $request->male_nickname;
            $female_nickname = $request->female_nickname;
            
            $credential = [
                'email' => $email,
                'password' => Hash::make($password),
                'male_nickname' => $male_nickname,
                'female_nickname' => $female_nickname
            ];
            $user = User::create($credential);
            if ($user) {
                // save couple
                $data_couple = [
                    'male_nickname' => $male_nickname,
                    'female_nickname' => $female_nickname,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now()
                ];
                $couple_id = Couple::insertGetId($data_couple);

                // create user by credential
                $combine_nickname = $female_nickname . '-' . $male_nickname;
                $access_token = $user->createToken($this->token_name . $combine_nickname)->accessToken;

                // get user setting
                $user_setting = CoupleMainSetting::with('couple')
                    ->where('couple_id', $couple_id)
                    ->first();
                
                $response = [
                    'user' => $user,
                    'userSetting' => $user_setting,
                    'token' => $access_token
                ];
                DB::commit();
                return $this->sendResponse($response);
            }

            DB::rollBack();
            return $this->sendFailedResponse('Gagal menyimpan data');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }

        
    /**
     * login User
     *
     * @param  mixed $request
     * @param String : email
     * @param String : password
     * @return void
     */
    public function login(Request $request) {
        // begin::validation
        unset($this->rules['email']);
        unset($this->rules['female_nickname']);
        unset($this->rules['male_nickname']);
        $this->rules->put('email', 'required')
            ->all();
        $validation = $this->validateReq($request, $this->rules->toArray(), $this->messageRules);
        if (isset($validation['error'])) {
            return $this->sendFailedValidation($validation['error']);
        }
        // end::validation

        try {
            $email = $request->email;
            $password = $request->password;
            
            $data = [
                'email' => $email,
                'password' => $password
            ];
            if (auth()->attempt($data)) {
                $user = auth()->user();
                $access_token = $user->createToken($this->token_name . 'login_' . $user->email)->accessToken;

                // get user setting
                $couple_id = Couple::select('id')
                    ->where('user_id', $user->id)
                    ->first()->couple_id;
                $user_setting = CoupleMainSetting::with('couple')
                    ->where('couple_id', $couple_id)
                    ->first();

                $response = [
                    'user' => $user,
                    'userSetting' => $user_setting,
                    'token' => $access_token
                ];
                return $this->sendResponse($response);
            } else {
                return $this->sendFailedResponse('Email belum terdaftar');
            }
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Get registered couple id
     *
     * @param  mixed $request
     * @return void
     */
    public function get_couple_id(Request $request)
    {
        $user = $request->user();
        $couple = Couple::select('id')
            ->where('user_id', $user->id)
            ->first();

        if ($couple) {
            return $couple->id;
        } else {
            return false;
        }
    }

    public function update_user_step(Request $request, $step) {
        $update = User::where('id', $request->user()->id)
            ->update(['step_setting_id' => $step, 'updated_at' => Carbon::now()]);
        
        return $update;
    }
    
    /**
     * Set setting theme for authentication user
     *
     * @param  mixed $request
     * @param int theme_id
     * @param int step_setting
     * @return void
     */
    public function update_theme(Request $request)
    {
        $user = $request->user();
        DB::beginTransaction();
        try {
            $step = $request->step_setting;
            $theme_id = $request->theme_id;
            $this->update_user_step($request, $step);

            $couple_id = $this->get_couple_id($request);
            CoupleMainSetting::updateOrCreate(
                ['couple_id' => $couple_id],
                [
                    'theme_id' => $theme_id,
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ]
            );

            DB::commit();
            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
        
    /**
     * Set category and invation link
     *
     * @param  mixed $request
     * @param int category_id
     * @param int step_setting
     * @param string link
     * @return void
     */
    public function update_link(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $step = $request->step_setting;
            $link = $request->link;
            $category_id = $request->category_id;

            $this->update_user_step($request, $step);

            $couple = $this->get_couple_id($request);

            $data = [
                'link' => $link,
                'category_id' => $category_id,
                'created_at' => Carbon::now()
            ];
            CoupleMainSetting::updateOrCreate(
                ['couple_id' => $couple],
                $data
            );
            
            DB::commit();
            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Update groom data and parents
     *
     * @param  mixed $request
     * @param string name
     * @param string parents
     * @param object photo
     * @param int step_setting
     * @return void
     */
    public function update_couple_data(Request $request, $type)
    {
        DB::beginTransaction();
        try {
            $name = $request->name;
            $parents = $request->parents;
            $photo = $request->photo;
            $step = $request->step_setting;
            $couple_id = $this->get_couple_id($request);
            $couple = Couple::find($couple_id);
            // photo
            if ($request->has('photo') && $photo != null && $photo != 'undefined') {
                $photo_ext = $photo->getClientOriginalExtension();
                $photo_name = $type . '_' . date('YmdHis') . '.' . $photo_ext;
                $photo_link = $this->upload($photo, $photo_name, 'couple');
                if ($photo_link != false) {
                    if ($type == 'groom') {
                        $deleted_file = $couple->male_photo;
                        $couple->male_photo = $photo_link;
                    } else if ($type == 'bride') {
                        $deleted_file = $couple->female_photo;
                        $couple->female_photo = $photo_link;
                    }
                }
            }

            if ($type == 'groom') {
                $couple->male_name = $name;
                $couple->male_parents = $parents;
            } else if ($type == 'bride') {
                $couple->female_name = $name;
                $couple->female_parents = $parents;
            }
            $couple->updated_at = Carbon::now();
            $couple->save();
            $this->update_user_step($request, $step);

            if (isset($deleted_file)) {
                File::delete($deleted_file);
            }
            DB::commit();
            return $this->sendResponse($couple);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Function to upload couple photo
     *
     * @param  mixed $request
     * @param object photo
     * @return void
     */
    public function update_couple_photo(Request $request)
    {
        DB::beginTransaction();
        try {
            $step = $request->step_setting;
            $this->update_user_step($request, $step);

            if ($request->has('photo')) {
                $photo = $request->photo;
                $photo_ext = $photo->getClientOriginalExtension();
                $name = 'couple_' . date('YmdHis') . '.' . $photo_ext;
                $upload = $this->upload($photo, $name, 'couple');
                $couple_id = $this->get_couple_id($request);
                $couple_main = CoupleMainSetting::select('id', 'couple_photo', 'couple_id')
                    ->where('couple_id', $couple_id)
                    ->first();
                if ($upload != false) {
                    $current_couple_photo = $couple_main->couple_photo;
                    $couple_main->couple_photo = $upload;
                    $couple_main->updated_at = Carbon::now();

                    if ($couple_main->save()) {
                        File::delete($current_couple_photo);
                    }

                } else {
                    return $this->sendFailedResponse('Upload foto gagal');
                }
            }

            DB::commit();
            return $this->sendResponse($couple_main);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Update detail event
     *
     * @param  mixed $request
     * @param  mixed $type
     * @return void
     */
    public function update_event(Request $request, $type)
    {
        DB::beginTransaction();
        try {
            $reception_date = $request->reception_date;
            $date_is_same = $request->date_is_same_with_reception;
            $address_is_same = $request->address_is_same_with_reception;
            $embed_is_same = $request->embed_is_same_with_reception;
            $time_zone = $request->time_zone;
            $reception_start = $request->reception_start;
            $reception_end = $request->reception_end;
            $until_finish = $request->until_finish;
            $address = $request->address;
            $lat = $request->latitude;
            $long = $request->longitude;
            $embed_maps = $request->embed_maps;
            $step = $request->step_setting;
            $couple_id = $this->get_couple_id($request);

            $this->update_user_step($request, $step);

            $data = [];
            $data = [
               $type . '_date' => $reception_date,
               $type . '_time_zone' => $time_zone,
               $type . '_start' => $reception_start,
               $type . '_end' => $reception_end,
               $type . '_until_finish' => $until_finish,
               $type . '_address' => $address,
               $type . '_embed_maps' => $embed_maps,
               'updated_at' => Carbon::now()
            ];

            if ($type == 'contract') {
                $data['contract_date_is_same_with_reception'] = $date_is_same;
                $data['contract_address_is_same_with_reception'] = $address_is_same;
                $data['contract_embed_is_same_with_reception'] = $embed_is_same;
            }
            CoupleMainSetting::where('couple_id', $couple_id)
                ->update($data);
            $couple_main = CoupleMainSetting::where('couple_id', $couple_id)
                ->first();

            DB::commit();
            return $this->sendResponse($couple_main);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Update step_setting_id in auth table
     *
     * @param  mixed $request
     * @param int step
     * @return void
     */
    public function set_progress_setup(Request $request)
    {
        try {
            $step = $request->step;
            $user = User::find(auth()->user()->id);
            $user->step_setting_id = $step;
            $user->updated_at = Carbon::now();
            $user->save();

            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }

    public function user_setting()
    {
        try {
            $user = auth()->user();
            $user_id = $user->id;
            $couple_id = Couple::select('id')
                ->where('user_id', $user_id)
                ->first()->id;
            $main_setting = CoupleMainSetting::with('couple')
                ->where('couple_id', $couple_id)
                ->first();

            return $this->sendResponse($main_setting);
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
}
