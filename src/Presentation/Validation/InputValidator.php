<?php

namespace App\Presentation\Validation;

use Psr\Http\Message\UploadedFileInterface;

class InputValidator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $ruleValue) {
                $value = $data[$field] ?? null;
                $this->applyRule($field, $value, $rule, $ruleValue);
            }
        }
        return empty($this->errors);
    }

    private function isFileField(string $field, array $data): bool
    {
        return isset($data[$field]) && is_array($data[$field]) && isset($data[$field]['tmp_name']);
    }

    private function validateFile(string $field, $file, string $rule, $ruleValue): void
    {
        if (!$file instanceof UploadedFileInterface) {
            $this->addError($field, "The uploaded file is not valid.");
            return;
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            $this->addError($field, "An error occurred while uploading the file.");
            return;
        }

        $error = match ($rule) {
            'required' => ($ruleValue && $file->getSize() === 0)
                ? "The {$field} field is required." : null,

            'maxSize' => ($ruleValue && $file->getSize() > $ruleValue)
                ? "The {$field} may not be greater than {$ruleValue} bytes." : null,
            default => null,
        };

        if ($error !== null) {
            $this->addError($field, $error);
        }
    }

    private function applyRule(string $field, $value, string $rule, $ruleValue): void
    {

        $error = match ($rule) {
            'required' => $ruleValue && empty($value)
                ? "The {$field} field is required." : null,
            'email' => $ruleValue && !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? "The {$field} must be a valid email address." : null,
            'numeric' => $ruleValue && !is_numeric($value)
                ? "The {$field} must be a number." : null,
            'array' => $ruleValue && !is_array($value)
                ? "The {$field} must be an array." : null,
            'min' => is_string($value) && strlen($value) < $ruleValue
                ? "The {$field} must be at least {$ruleValue} characters." : null,
            'max' => is_string($value) && strlen($value) > $ruleValue
                ? "The {$field} may not be greater than {$ruleValue} characters." : null,
            default => null,
        };

        if ($error !== null) {
            $this->addError($field, $error);
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
