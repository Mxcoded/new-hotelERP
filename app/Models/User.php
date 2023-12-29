<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;


class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasPermissions,  HasFactory, Notifiable;

   // Relationship with properties (assuming a user can be associated with multiple properties)
   public function properties()
   {
       return $this->belongsToMany(Property::class);
   }

   // Assuming users might be linked to reservations
   public function reservations()
   {
       return $this->hasMany(Reservation::class);
   }

   // Managing messages sent and received by the user
   public function sentMessages()
   {
       return $this->hasMany(Message::class, 'sender_id');
   }

   public function receivedMessages()
   {
       return $this->hasMany(Message::class, 'receiver_id');
   }

   // Add other model properties/methods as needed

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'property_id',
        'employee_id',
        'position',
        'department',
        'active',
        'login_access',
        'phone_number',
        'address',
        'hire_date',
        'termination_date',
        'created_by',
        'updated_by',
        // Include any other fields as required
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'login_access' => 'boolean',
        // other casts
    ];

    protected $dates = [
        'hire_date',
        'termination_date'
    ];
}
