<?php

namespace IWD\CartToQuote\Model\Api;

/**
 * List of errors
 *
 * Class Error
 * @package IWD\CartToQuote\Model\Api
 */
class Error
{
    const RESPONSE_WITHOUT_ERROR = 0;
    const UNTREATED_ERROR = 1000;

    const KEY_DOES_NOT_SET = 1001;
    const KEY_DID_NOT_COME = 1002;
    const KEY_IS_NOT_CORRECT = 1003;
    const TOKEN_DID_NOT_COME = 1004;
    const TOKEN_IS_NOT_CORRECT = 1005;
    const ADMIN_KEY_DID_NOT_COME = 1006;
    const ADMIN_KEY_IS_NOT_CORRECT = 1007;
    const USER_KEY_DID_NOT_COME = 1008;
    const USER_KEY_IS_NOT_CORRECT = 1009;

    const REQUEST_PARAM_DOES_NOT_EXISTS = 1010;

    const USER_DOES_NOT_EXISTS = 1011;
    const ADMIN_DOES_NOT_EXISTS = 1012;
    const STORE_DOES_NOT_EXISTS = 1013;
    const STORE_EXISTS = 1014;
    const STORE_EXISTS_FOR_THIS_EMAIL = 1015;
    const STORE_CANNOT_CREATE = 1016;
    const STORE_API_LICENSE_ERROR = 1017;

    const HTTP_PAGE_NOT_FOUND = 404;
}
