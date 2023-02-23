<?php

namespace Orvital\Extensions\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Taken from https://github.com/korridor/laravel-model-validation-rules
 */
class UniqueEloquent implements Rule
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var string|null
     */
    private $key;

    /**
     * @var Closure|null
     */
    private $builderClosure;

    /**
     * @var string
     */
    private $attribute;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $ignoreId;

    /**
     * @var string
     */
    private $ignoreColumn;

    /**
     * Custom validation message.
     *
     * @var string|null
     */
    private $message = null;

    /**
     * @var bool|null
     */
    private $messageTranslated = null;

    /**
     * UniqueEloquent constructor.
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->value = $value;
        /** @var Model|Builder $builder */
        $builder = new $this->model();
        $modelKeyName = $builder->getKeyName();
        $builder = $builder->where(null === $this->key ? $modelKeyName : $this->key, $value);
        if (null !== $this->builderClosure) {
            $builderClosure = $this->builderClosure;
            $builder = $builderClosure($builder);
        }
        if (null !== $this->ignoreId) {
            $builder = $builder->where(
                null === $this->ignoreColumn ? $modelKeyName : $this->ignoreColumn,
                '!=',
                $this->ignoreId
            );
        }

        return 0 === $builder->count();
    }

    /**
     * Set a custom validation message.
     */
    public function setMessage(string $message, bool $translated): void
    {
        $this->message = $message;
        $this->messageTranslated = $translated;
    }

    /**
     * Set a custom validation message.
     *
     * @return $this
     */
    public function withMessage(string $message): self
    {
        $this->setMessage($message, false);

        return $this;
    }

    /**
     * Set a translated custom validation message.
     *
     * @return $this
     */
    public function withCustomTranslation(string $translationKey): self
    {
        $this->setMessage($translationKey, true);

        return $this;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): string
    {
        if ($this->message !== null) {
            if ($this->messageTranslated) {
                return trans(
                    $this->message,
                    [
                        'attribute' => $this->attribute,
                        'model' => strtolower(class_basename($this->model)),
                        'value' => $this->value,
                    ]
                );
            } else {
                return $this->message;
            }
        } else {
            return trans(
                'validation.unique_model',
                [
                    'attribute' => $this->attribute,
                    'model' => strtolower(class_basename($this->model)),
                    'value' => $this->value,
                ]
            );
        }
    }

    public function setBuilderClosure(?Closure $builderClosure): void
    {
        $this->builderClosure = $builderClosure;
    }

    /**
     * @return $this
     */
    public function query(Closure $builderClosure): self
    {
        $this->setBuilderClosure($builderClosure);

        return $this;
    }

    /**
     * @param  mixed  $id
     */
    public function setIgnore($id, ?string $column = null): void
    {
        $this->ignoreId = $id;
        $this->ignoreColumn = $column;
    }

    public function ignore($id, ?string $column = null): self
    {
        $this->setIgnore($id, $column);

        return $this;
    }
}
