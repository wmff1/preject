<?php

namespace app\common\validate\Business;

use App\Http\Requests\BaseRequest;
use think\Request;
use think\Validate;

class UserLogin extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [];

        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
                break;
            }
            case 'POST':
            {
                $rules = [
                    'mobile' => ['require','regex:/^1[3456789]{1}\d{9}$/','unique:business'],
                    'password' => 'require',
                ];

                break;
            }
            case 'PUT':
            case 'PATCH':
            {
                break;
            }
            default:break;
        }

        return $rules;

    }
}
