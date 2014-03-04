<?php

namespace TwentyFifth\Serializing\TestModel;

use TwentyFifth\Serializing\AnnotationSerializable;

/**
 * @method int getIdWorks()
 * @method string getNameWorks()
 * @method TestModel getModelWorks()
 * @method some getWithNoParenthesisWorks
 * @method some spaceWorks in between
 * @method some setSetWorks
 * @method noExistentType getNonExistentTypeWorks()
 * @method void withTypeHintWorks(String $hint)
 * @method string getDoublesWorks()
 * @method string getDoublesWorks()
 * @method integer getDoublesWorks()
 * @method boolean isIsWorks()
 *
 * @Method some spaceFails in between
 * @method 123 getNumbersCaseFails()
 * @method what? whatFails
 * @meTHOd string setSomethingFails()
 * @methods string getThisFails()
 *
 *
 * @noSerialize simpleWorks
 * @noSerialize getDoublesWorks
 * @noSerialize getDoublesWorks
 * @noSerialize getthisalllowercaseWorks
 * @noSerialize GETTHISALLUPPERCASEWORKS
 *
 * @noSerialize getDoubles some other valueFails
 * @noSerialize getFinalModel finalModelFails
 * @noSerialize getWithParenthesisFails()
 * @NOSerialize getThisAnnotationCaseFails
 * @NOSERIALIZE getThisAnnotationCaseFails
 * @noserialize getThisAnnotationCaseFails
 *
 */
class TestAnnotatedModel
	implements AnnotationSerializable
{}