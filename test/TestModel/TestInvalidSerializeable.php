<?php

namespace TwentyFifth\Serializing\TestModel;

use TwentyFifth\Serializing\Serializable;

interface InvalidSerializeable extends Serializable {}

class TestInvalidSerializeable implements InvalidSerializeable
{}