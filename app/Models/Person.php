<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'people';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'alternative_names' => AsArrayObject::class,
        'birth_date'        => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'full_given_name',
    ];

    /**
     * Returns the full name of the person.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $lastNameEmpty = empty($this->last_name);
        $firstNameEmpty = empty($this->first_name);

        if ($lastNameEmpty && !$firstNameEmpty) {
            return $this->first_name;
        } else if ($firstNameEmpty && !$lastNameEmpty) {
            return $this->last_name;
        } else if ($firstNameEmpty && $lastNameEmpty) {
            return '';
        }

        return $this->last_name . ', ' . $this->first_name;
    }

    /**
     * Returns the full given name of the person.
     *
     * @return string
     */
    public function getFullGivenNameAttribute(): string
    {
        $familyNameEmpty = empty($this->family_name);
        $givenNameEmpty = empty($this->given_name);

        if ($familyNameEmpty && !$givenNameEmpty) {
            return $this->given_name;
        } else if ($givenNameEmpty && !$familyNameEmpty) {
            return $this->family_name;
        } else if ($givenNameEmpty && $familyNameEmpty) {
            return '';
        }

        return $this->family_name . ', ' . $this->given_name;
    }
}
