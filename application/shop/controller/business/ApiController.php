<?php

namespace app\shop\controller\business;

use think\Controller;

class ApiController extends Controller
{

    protected $statusCode = 200;

    /**
     * @param $statusCode
     * @return \App\Controllers\Api\Base\ApiController
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * The response status code
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }


    /**
     * @param $data
     * @return mixed
     */
    public function respondWithData($data)
    {
        return ( $data )
            ->response()
            ->setStatusCode($this->getStatusCode());
    }

    public function json($data = [], $status = 200, array $headers = [], $options = 0){
        return json_encode($data);
    }

    /**
     * @param string $message
     * @return array
     */
    public function respondWithSuccess($message = 'Success !!')
    {
        $data = [
            'error'         => false,
            'message'       => $message,
            'status_code'   => $this->getStatusCode()
        ];

        // return (\Response::json($data))->setStatusCode($this->getStatusCode());
        return $this->json($data);
        // return json_encode($data);
    }

    /**
     * @param string $message
     * @return array
     */
    public function respondWithError($message = "There was an error")
    {
        $data = [
            'error'         => true,
            'message'       => $message,
            'status_code'   => $this->getStatusCode()
        ];

//        return (\Response::json($data))->setStatusCode($this->getStatusCode());
        // return (\Response::json($data))->setStatusCode(200);
        return json_encode($data);

    }

    /**
     * Provided json body is not formatted as per api requirement.
     * @param string $message
     * @return mixed
     */
    public function respondWrongFormat($message = "JSON data is not well formatted.")
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * When a non supported search parameter is requested
     * @param string $message
     * @return mixed
     */
    public function respondWrongParameter ($message = "You requested a non supported search parameter!")
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * Some operation (save) failed.
     * @param string $message
     * @return mixed
     */
    public function respondNotSaved($message = "Not Saved !")
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * When a missing resource is requested
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = "Not Found !")
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * There was an internal error
     * @param string $message
     * @return mixed
     */
    public function respondInternalError($message = "Internal Server Error !!")
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * Cleans up url variables to eliminate spaces
     * @param $string
     * @return array
     */
    public function formatFields($string)
    {
        return explode(",", preg_replace('/\s*,\s*/', ',', rtrim(trim($string), ',')));
    }

    /**
     * @param $amount
     * @return string
     */
    public function formatMoney($amount) {
        return number_format($amount, $this->amountDecimal(), $this->amountDecimalSeparator(), $this->amountThousandSeparator());
    }

    /**
     * @param $date
     * @return false|string
     */
    public function formatDate($date){
        return $new_date_format = date($this->dateFormat(), strtotime($date));
    }

}
