<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public function businessLocation()
    {
        return $this->hasOneThrough(
            \App\BusinessLocation::class, // Final model
            \App\Contact::class, // Intermediate model
            'id', // Foreign key on Contact (parent) table...
            'business_id', // Foreign key on BusinessLocation table...
            'contact_id', // Local key on ContactPerson (this model)...
            'business_id' // Local key on Contact table
        );
    }

    /**
     * Get all business locations associated with the contact person.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function businessLocations()
    {
        return $this->hasManyThrough(
            \App\BusinessLocation::class,
            \App\Contact::class,
            'id', // Local key on Contact (related to contact_id in ContactPerson)
            'business_id', // Foreign key on BusinessLocation
            'contact_id', // Foreign key on ContactPerson (points to Contact)
            'business_id'  // Local key on Contact (used to match with BusinessLocation)
        );
    }

}
