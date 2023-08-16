<?php

namespace Marvel\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Marvel\Database\Models\Settings;
use Marvel\Exceptions\MarvelException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class EnsureEmailIsVerifiedDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @ensureEmailIsVerified on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $resolver = $fieldValue->getResolver();

        // If you have any work to do that does not require the resolver arguments, do it here.
        // This code is executed only once per field, whereas the resolver can be called often.

        $fieldValue->setResolver(function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($resolver) {
            // Do something before the resolver, e.g. validate $args, check authentication

            /* This is checking if the user is logged in and if the user has verified his email. If
            not, it throws an exception. */
            $setting = Settings::first();
            $useMustVerifyEmail = isset($setting->options['useMustVerifyEmail']) ? $setting->options['useMustVerifyEmail'] : false;
            if($useMustVerifyEmail && $context->user() && $context->user() instanceof MustVerifyEmail && !$context->user()->hasVerifiedEmail()){
                throw new MarvelException(EMAIL_NOT_VERIFIED, EMAIL_NOT_VERIFIED);
            }
            // Call the actual resolver
            $result = $resolver($root, $args, $context, $resolveInfo);

            // Do something with the result, e.g. transform some fields

            return $result;
        });

        // Keep the chain of adding field middleware going by calling the next handler.
        // Calling this before or after ->setResolver() allows you to control the
        // order in which middleware is wrapped around the field.
        return $next($fieldValue);
    }
}