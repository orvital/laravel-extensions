<?php

namespace Orvital\Extensions\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Taken from https://github.com/korridor/laravel-model-validation-rules
 */
class UniqueEloquent implements ValidationRule
{
    /**
     * Class name of model.
     *
     * @var class-string<Model>
     */
    private string $model;

    /**
     * Relevant key in the model.
     */
    private ?string $key;

    /**
     * Closure that can extend the eloquent builder
     */
    private ?Closure $builderClosure;

    private mixed $ignoreId = null;

    private ?string $ignoreColumn = null;

    /**
     * Custom validation message.
     */
    private ?string $customMessage = null;

    /**
     * Translation key for custom validation message.
     */
    private ?string $customMessageTranslationKey = null;

    /**
     * UniqueEloquent constructor.
     *
     * @param  class-string<Model>  $model Class name of model.
     * @param  string|null  $key Relevant key in the model.
     * @param  Closure|null  $builderClosure Closure that can extend the eloquent builder
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * Determine if the validation rule passes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
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

        if ($builder->exists()) {
            if ($this->customMessage !== null) {
                $fail($this->customMessage);
            } else {
                $fail($this->customMessageTranslationKey ?? 'modelValidationRules::validation.unique_model')->translate([
                    'attribute' => $attribute,
                    'model' => strtolower(class_basename($this->model)),
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Set a custom validation message.
     *
     * @return $this
     */
    public function withMessage(string $message): self
    {
        $this->customMessage = $message;

        return $this;
    }

    /**
     * Set a translated custom validation message.
     *
     * @return $this
     */
    public function withCustomTranslation(string $translationKey): self
    {
        $this->customMessageTranslationKey = $translationKey;

        return $this;
    }

    /**
     * Set a closure that can extend the eloquent builder.
     */
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

    public function setIgnore(mixed $id, ?string $column = null): void
    {
        $this->ignoreId = $id;
        $this->ignoreColumn = $column;
    }

    public function ignore(mixed $id, ?string $column = null): self
    {
        $this->setIgnore($id, $column);

        return $this;
    }
}
