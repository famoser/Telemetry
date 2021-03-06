<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 13:23
 */

namespace Famoser\MassPass\Types;


class ApiErrorTypes
{
//[Description("No API error occured")]
    const None = 0;

    #region request errors
//[Description("Api Version unknown")]
    const ApiVersionInvalid = 100;

//[Description("Json request could not be deserialized")]
    const RequestFailure = 101;

//[Description("Request could not be processed by the server. This is probably a API error, nothing you can do about it :/")]
    const ServerFailure = 102;

//[Description("Json request could not be deserialized")]
    const RequestUriInvalid = 103;

//[Description("Execution of request was denied")]
    const Forbidden = 104;

//[Description("Some required properties were missing")]
    const NotWellDefined = 105;

//[Description("A failure occured on the server while accessing the database")]
    const DatabaseFailure = 106;
    #endregion

//[Description("This Content Type is unknown")]
    const UnknownContentType = 1001;
}