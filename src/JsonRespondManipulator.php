<?php
namespace RTLer\Oauth2;

use Psr\Http\Message\ResponseInterface;

class JsonRespondManipulator
{
    protected $respond;

    /**
     * RespondManipulator constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->respond = $response;
    }

    /**
     * edit Respond body
     *
     * @param $callback
     * @return $this
     */
    public function editBody($callback)
    {
        $bodyObject = $this->respond->getBody();
        $responseBody = json_decode((string)$bodyObject, true);

        $editedResponseBody = $callback($responseBody);
        $bodyObject->detach();

        $bodyObject->attach('php://temp', 'wb+');
        $bodyObject->write(json_encode($editedResponseBody));

        return $this;
    }

    /**
     * edit response it self (edit header and etc.)
     *
     * @param $callback
     * @return $this
     */
    public function editResponse($callback)
    {
        $this->respond = $callback($this->respond);

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getRespond()
    {
        return $this->respond;
    }
}