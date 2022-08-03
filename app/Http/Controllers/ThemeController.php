<?php
/**
 * Author Ilham Meru Gumilang
 * 30 Juli 2022
 * 7:48 PM
 * Indonesia
 */

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleMainSetting;
use App\Models\Theme;
use App\Models\ThemeDetail;
use App\Traits\ResponseTrait;
use App\Traits\UploadFileTrait;
use App\Traits\ValidationRequestTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    use ResponseTrait;
    use ValidationRequestTrait;
    use UploadFileTrait;

    private $rules;
    private $message_rules;

    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'path_to_theme' => 'required',
            'path_to_background' => 'required'
        ];
        $this->message_rules = [
            'name.required' => 'Nama harus diisi',
            'path_to_theme.*' => 'File harus diisi',
            'path_to_background' => 'Background harus diisi'
        ];
    }

    /**
     * Get list of themes
     * 
     * @return void
     */
    public function index() {
        try {
            $data = Theme::select('id','name')
                ->with('detail:id,theme_id,path_to_theme,path_to_background')
                ->get();
            $user_id = auth()->user()->id;
            $couple_id = Couple::select('id')
                ->where('user_id', $user_id)
                ->first()->id;
            $selected_theme = CoupleMainSetting::select('theme_id')
                ->where('couple_id', $couple_id)
                ->first()->theme_id;
            $data = collect($data)->map(function($item) use($selected_theme) {
                if ($selected_theme != null) {
                    if ($selected_theme == $item['id']) {
                        $item['user_select'] = true;
                    } else {
                        $item['user_select'] = false;
                    }
                } else {
                    $item['user_select'] = false;
                }
                return $item;
            })->all();
            return $this->sendResponse([
                'themes' => $data,
                'selected_theme' => $selected_theme
            ]);        
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Save theme image
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // begin::validation
        $validation = $this->validateReq($request, $this->rules, $this->message_rules);
        if (isset($validation['error'])) {
            return $this->sendFailedValidation($validation['error']);
        }
        // end::validation

        DB::beginTransaction();
        try {
            $name = $request->name;
            $file = $request->path_to_theme;
            $background = $request->path_to_background;
            
            if (count($file) != count($background)) {
                return $this->sendFailedResponse('Jumlah background dan tema harus sama');
            }

            $theme = Theme::insertGetId(['name' => $name, 'created_at' => Carbon::now()]);

            $data_detail = [];
            for ($a = 0; $a < count($file); $a++) {
                $short_name = strtolower(explode(' ', $name)[0]);
                // theme names
                $theme_ext = $file[$a]->getClientOriginalExtension();
                $theme_name = $short_name . '_theme_' . date('YmdHi') . '.' . $theme_ext;
                $upload_theme = $this->upload($file[$a], $theme_name, 'theme');
                if ($upload_theme == false) {
                    return $this->sendFailedResponse('Gagal menyimpan gambar');
                }
                // background names
                $background_ext = $background[$a]->getClientOriginalExtension();
                $background_name = $short_name . '_background_' . date('YmdHi') . '.' . $background_ext;
                $upload_background = $this->upload($background[$a], $background_name, 'theme');
                if ($upload_background == false) {
                    return $this->sendFailedResponse('Gagal menyimpan gambar');
                }

                $data_detail[] = [
                    'theme_id' => $theme,
                    'path_to_theme' => $upload_theme,
                    'path_to_background' => $upload_background,
                    'created_at' => Carbon::now()
                ];
            }

            ThemeDetail::insert($data_detail);
            
            DB::commit();
            return $this->sendResponse($data_detail);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $theme = Theme::find($id);
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    
    /**
     * Delete theme by ID
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $theme = Theme::find($id);
            ThemeDetail::where('theme_id', $id)->delete();
            $theme->delete();

            DB::commit();
            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendFailedResponse($th->getMessage());
        }
    }
}
