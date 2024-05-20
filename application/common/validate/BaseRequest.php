<?php

namespace App\Http\Requests;

use think\exception\ValidateException;
use think\Request;
use think\response\Json;
use think\Validate;

class BaseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Overrides response from the FormRequest
     * to not redirect for our API development
     * @param array $errors
     * @return Json|\Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        $message = array(
            'message' => "There were validation errors",
            'errors' => $errors
        );
        return new Json($message, 200);
    }

    protected function failedValidation(Validate $validator)
    {
        throw new ValidateException($validator,
            $this->response(array('msg'=>$validator->errors()->first()))
        );
//        throw new ValidationException($validator,
//            $this->response(array('msg'=>'hello'))
//        );
    }
}
