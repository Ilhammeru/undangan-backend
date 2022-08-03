<?php

namespace App\Http\Controllers;

use App\Models\CategoryInvitation;
use App\Models\CoupleMainSetting;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryInvitationController extends Controller
{
    use ResponseTrait;
    
    /**
     * Show all category invitation data
     *
     * @return void
     */
    public function index()
    {
        try {
            $data = CategoryInvitation::select('id', 'name')
                ->get();
            return $this->sendResponse($data);
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Store category data
     *
     * @param  mixed $request
     * @param string name
     * @return void
     */
    public function store(Request $request)
    {
        try {
            $name = $request->name;

            // validation
            $check = CategoryInvitation::select('id')
                ->where('name', $name)
                ->first();
            if ($check) {
                return $this->sendFailedResponse('Nama kategori sudah terdaftar');
            }
            
            $data = [
                'name' => $name,
                'created_at' => Carbon::now()
            ];
            CategoryInvitation::insert($data);
            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
    
    /**
     * Update category invitation date
     *
     * @param  mixed $request
     * @param  int $id
     * @param string name
     * @return void
     */
    public function update(Request $request, $id)
    {
        try {
            $name = $request->name;
            $category = CategoryInvitation::find($id);
            if (strtolower($name) != strtolower($category->name)) {
                return $this->sendFailedResponse(['error' => 'Nama sudah ada di database']);
            }

            $category->name = $name;
            $category->updated_at = Carbon::now();
            $category->save();

            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            return $this->sendResponse($th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // begin::validation
            $check = CoupleMainSetting::select('id')
                ->where('category_id', $id)
                ->first();
            if ($check) {
                return $this->sendFailedResponse('Sudah ada undangan yang memakai kategori ini');
            }
            // end::validation
            CategoryInvitation::find($id)->delete();
            return $this->sendResponse([]);
        } catch (\Throwable $th) {
            return $this->sendFailedResponse($th->getMessage());
        }
    }
}
