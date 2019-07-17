<?php

use Mockery as m;
use Astrotomic\Translatable\Test\Request\DummyFormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class TranslatableFormRequestTest extends TestsBase
{
    public function test_it_generate_all_rules_by_locale()
    {
        $factory = m::mock(ValidationFactory::class);

        $factory->shouldReceive('make')
            ->with([], [
                'name' => 'required',
                'title' => 'required|array',
                'title.el' => 'string',
                'title.en' => 'string',
                'title.fr' => 'string',
                'title.de' => 'string',
                'title.id' => 'string',
                'title.en-GB' => 'string',
                'title.en-US' => 'string',
                'title.de-DE' => 'string',
                'title.de-CH' => 'string',
            ], [], []);

        $request = DummyFormRequest::createFromGlobals()
            ->setContainer(app());

        static::getMethod('createDefaultValidator')->invokeArgs($request, [$factory]);
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass(DummyFormRequest::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
