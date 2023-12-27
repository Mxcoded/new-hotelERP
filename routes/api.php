<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RateTypeController;
use App\Http\Controllers\HousekeepingLogController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AllotmentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GuestPreferenceController;
use App\Http\Controllers\LoyaltyProgramController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\RoomAllocationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\CancellationPolicyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
});



/**
 * Declearing route base on role with middleware group
 * Here we have the role of Super admin delcare
 * meanining a user user must possess the role of super admin
 * and necessary permission to perform any of the action within
 * the group!
 */
// Route::middleware(['auth:sanctum', 'role:admin|editor'])->group(function () {
//     Route::get('/api/posts', 'PostController@index')->middleware('can:view posts');
//     Route::post('/api/posts', 'PostController@store')->middleware('can:create posts');
//     Route::put('/api/posts/{id}', 'PostController@update')->middleware('can:update posts');
//     Route::delete('/api/posts/{id}', 'PostController@destroy')->middleware('can:delete posts');
// });

// Route::group(['middleware' => ['role:manager']], function () {
//     //Some route that is generally accessible to every user with the role of Manager
//     Route::group(['middleware' => ['can:publish articles']], function () {
//         //Some route to perform some certain action
//     });
// });

/**
 * User API Routes
 */
// Grouping User routes under '/users' prefix
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']); // List Users with Roles
    Route::get('/{id}', [UserController::class, 'show']); // Get User Details
    Route::post('/', [UserController::class, 'store']); // Create User with Role
    Route::put('/{id}', [UserController::class, 'update']); // Update User and Role
    Route::delete('/{id}', [UserController::class, 'destroy']); // Delete User
    Route::post('/{id}/assign-property', [UserController::class, 'assignProperty']); // Assign User to Property
    Route::get('/properties/{propertyId}', [UserController::class, 'listUsersByProperty']); // List Employees Assigned to a Property
    Route::put('/{id}/update-permissions', [UserController::class, 'updatePermissions']); // Update User Permissions
});

/**
 * Roles & Permission
 *
 */
// Roles & Permissions
Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);


/**
 * Proerty API Routes
 */
// Grouping Property routes under '/properties' prefix
Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index']); // List Properties
    Route::get('/{id}', [PropertyController::class, 'show']); // Get Property Details
    Route::post('/', [PropertyController::class, 'store']); // Create Property
    Route::put('/{id}', [PropertyController::class, 'update']); // Update Property
    Route::delete('/{id}', [PropertyController::class, 'destroy']); // Delete Property
    Route::get('/{propertyId}/reservations', [PropertyController::class, 'listReservationsByProperty']);// List Reservations by Property
    Route::get('/{propertyId}/rates', [PropertyController::class, 'listRatesByProperty']); // List Rates by Property
    // Property-related facility management
    Route::post('/{propertyId}/facilities', [PropertyController::class, 'addFacility']);
    Route::put('/{propertyId}/facilities/{facilityId}', [PropertyController::class, 'updateFacility']);
    Route::delete('/{propertyId}/facilities/{facilityId}', [PropertyController::class, 'removeFacility']);
});


/**
 * Facility API Routes
 * Grouping Facility routes under '/facilities' prefix
 */
Route::prefix('facilities')->group(function () {
    Route::get('/', [FacilityController::class, 'index']);
    Route::put('/{facilityId}/configure', [FacilityController::class, 'configureFacility']);
    Route::post('/{facilityId}/book', [FacilityController::class, 'bookFacility']);
    Route::delete('/facility-bookings/{bookingId}/cancel', [FacilityController::class, 'cancelBooking']);
    Route::get('/{facilityId}/availability', [FacilityController::class, 'checkAvailability']);
    Route::put('/{facilityId}/update-availability', [FacilityController::class, 'updateAvailability']);
    Route::get('/{facilityId}/usage-report', [FacilityController::class, 'generateUsageReport']);
    Route::post('/{facilityId}/maintenance-request', [FacilityController::class, 'submitMaintenanceRequest']);
    Route::post('/{facilityId}/feedback', [FacilityController::class, 'submitFeedback']);
});


/**
 * Floor API Routes
 */
// Grouping Floor routes under '/floors' prefix
Route::prefix('floors')->group(function () {
    Route::get('/', [FloorController::class, 'index']); // Fetch all floors
    Route::get('/{id}', [FloorController::class, 'show']); // Fetch a specific floor by ID
    Route::post('/', [FloorController::class, 'store']); // Create a new floor
    Route::put('/{id}', [FloorController::class, 'update']); // Update an existing floor
    Route::delete('/{id}', [FloorController::class, 'destroy']); // Delete a floor
    Route::get('/properties/{propertyId}', [FloorController::class, 'listFloorsByProperty']); //list room by property
});


/**
 * Room API Routes
 */
// Grouping Room routes under '/rooms' prefix
Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']);// List Rooms
    Route::get('/{id}', [RoomController::class, 'show']);// Get Room Details
    Route::post('/', [RoomController::class, 'store']); // Create Room
    Route::put('/{id}', [RoomController::class, 'update']); // Update Room
    Route::delete('/{id}', [RoomController::class, 'destroy']); // Delete Room
    Route::get('/properties/{propertyId}', [RoomController::class, 'listRoomsByProperty']);// List Rooms by Property
    Route::put('/{id}/status', [RoomController::class, 'updateStatus']); // Update Room Status
    Route::get('/type/{typeId}', [RoomController::class, 'listRoomsByType']); // List Rooms by Type
});



/**
 * RoomType API Routes*
 * Grouping RoomType routes under '/room-types' prefix
 */
Route::prefix('room-types')->group(function () {
    Route::get('/', [RoomTypeController::class, 'index']); // List Room Types
    Route::get('/{id}', [RoomTypeController::class, 'show']); // Get Room Type Details
    Route::post('/', [RoomTypeController::class, 'store']); // Create Room Type
    Route::put('/{id}', [RoomTypeController::class, 'update']); // Update Room Type
    Route::delete('/{id}', [RoomTypeController::class, 'destroy']); // Delete Room Type
    Route::get('/{typeId}/rooms', [RoomTypeController::class, 'listRoomsOfSpecificType']); // List Rooms of a Specific Type
    Route::post('/{typeId}/rate', [RoomTypeController::class, 'linkRoomTypeToRate']); // Link Room Type to Rate
    Route::get('/{typeId}/rates', [RoomTypeController::class, 'listRatesByRoomType']); // List Rates by Room Type
});


/**
 * Booking API Routes
 */
// Grouping Booking routes under '/bookings' prefix
Route::prefix('bookings')->group(function () {
    Route::post('/', [BookingController::class, 'createBooking']);
    Route::put('/{bookingId}', [BookingController::class, 'updateBooking']);
    Route::delete('/{bookingId}', [BookingController::class, 'deleteBooking']);
    Route::get('/{bookingId}', [BookingController::class, 'getBookingDetails']);
});


/**
 * Reservation API Routes
 */
// Grouping Reservation routes under '/reservations' prefix
Route::prefix('reservations')->group(function () {
    Route::get('/', [ReservationController::class, 'index']); // List Reservations
    Route::get('/{id}', [ReservationController::class, 'show']); // Get Reservation Details
    Route::post('/', [ReservationController::class, 'store']); // Create Reservation
    Route::put('/{id}', [ReservationController::class, 'update']); // Update Reservation
    Route::delete('/{id}', [ReservationController::class, 'destroy']); // Cancel Reservation
    Route::post('/availability', [ReservationController::class, 'checkAvailability']); // Check Reservation Availability
    Route::get('/properties/{propertyId}', [ReservationController::class, 'listReservationsByProperty']); // List Reservations for a Property
});


/**
 * Guest API Routes
 * Grouping Guest routes under '/guests' prefix
 */
Route::prefix('guests')->group(function () {
    Route::get('/', [GuestController::class, 'index']);// List Guests
    Route::get('/{id}', [GuestController::class, 'show']); // Get Guest Details
    Route::post('/', [GuestController::class, 'store']); // Create Guest Profile
    Route::put('/{id}', [GuestController::class, 'update']); // Update Guest Profile
    Route::delete('/{id}', [GuestController::class, 'destroy']); // Delete Guest Profile
    Route::get('/{guestId}/reservations', [GuestController::class, 'listReservationsByGuest']);// List Reservations by Guest
    Route::put('/{id}/preferences', [GuestController::class, 'updatePreferences']);// Update Guest Preferences
    Route::post('/{guestId}/feedback', [GuestController::class, 'recordFeedback']);// Record Guest Feedback
});


//Cancellation polices
Route::post('/cancellation-policies', [CancellationPolicyController::class, 'store']);


/**
 * Checkin API Routes
 * Grouping CheckIn routes under '/check-ins' prefix
 */
Route::prefix('check-ins')->group(function () {
    Route::get('/', [CheckInController::class, 'index']);// List Check-Ins
    Route::get('/{id}', [CheckInController::class, 'show']); // Get Check-In Details
    Route::post('/', [CheckInController::class, 'store']);// Record Check-In
    Route::put('/{id}', [CheckInController::class, 'update']);// Update Check-In
    Route::delete('/{id}', [CheckInController::class, 'destroy']);// Delete Check-In Record
    Route::get('/properties/{propertyId}', [CheckInController::class, 'listCheckInsByProperty']);// List Check-Ins by Property
    Route::get('/guests/{guestId}', [CheckInController::class, 'listCheckInsByGuest']);// List Check-Ins by Guest
    Route::post('/direct', [CheckInController::class, 'handleDirectCheckIn']);// Handle Direct Check-In
});


/**
 * CheckOut API Routes
 */
// Grouping CheckOut routes under '/check-outs' prefix
Route::prefix('check-outs')->group(function () {
    Route::get('/', [CheckOutController::class, 'index']); // List Check-Outs
    Route::get('/{id}', [CheckOutController::class, 'show']); // Get Check-Out Details
    Route::post('/', [CheckOutController::class, 'store']); // Record Check-Out
    Route::put('/{id}', [CheckOutController::class, 'update']); // Update Check-Out
    Route::delete('/{id}', [CheckOutController::class, 'destroy']); // Delete Check-Out Record
    Route::get('/properties/{propertyId}', [CheckOutController::class, 'listCheckOutsByProperty']); // List Check-Outs by Property
    Route::get('/guests/{guestId}', [CheckOutController::class, 'listCheckOutsByGuest']);// List Check-Outs by Guest
    Route::put('/{id}/finalize-billing', [CheckOutController::class, 'finalizeBilling']);// Finalize Billing for Check-Out
});


/**
 * Folio API Routes
 */
// Grouping Folio routes under '/folios' prefix
Route::prefix('folios')->group(function () {
    Route::get('/', [FolioController::class, 'index']);// List Folios
    Route::get('/{id}', [FolioController::class, 'show']);// Get Folio Details
    Route::post('/', [FolioController::class, 'store']); // Create Folio
    Route::put('/{id}', [FolioController::class, 'update']);// Update Folio
    Route::delete('/{id}', [FolioController::class, 'destroy']);// Delete Folio
    Route::post('/{id}/charges', [FolioController::class, 'addCharge']);// Add Charge to Folio
    Route::post('/{id}/payments', [FolioController::class, 'processPayment']);// Process Payment on Folio
    Route::get('/guests/{guestId}', [FolioController::class, 'listFoliosByGuest']);// List Folios by Guest
    Route::get('/properties/{propertyId}', [FolioController::class, 'listFoliosByProperty']);// List Folios by Property
});


/**
 * Rate API Routes
 */
// Grouping Rate routes under '/rates' prefix
Route::prefix('rates')->group(function () {
    Route::get('/', [RateController::class, 'index']); // List Rates
    Route::get('/{id}', [RateController::class, 'show']);// Get Rate Details
    Route::post('/', [RateController::class, 'store']);// Create Rate
    Route::put('/{id}', [RateController::class, 'update']);// Update Rate
    Route::delete('/{id}', [RateController::class, 'destroy']);// Delete Rate
    Route::post('/{rateId}/promotions/{promotionId}', [RateController::class, 'linkRateToPromotion']);// Link Rate to Promotion
    Route::put('/{id}/adjust', [RateController::class, 'adjustRate']);// Adjust Rate for Specific Conditions
});


/**
 * RateType API Routes
 */
// Grouping RateType routes under '/rate-types' prefix
Route::prefix('rate-types')->group(function () {
    Route::get('/', [RateTypeController::class, 'index']);// List Rate Types
    Route::get('/{id}', [RateTypeController::class, 'show']);// Get Rate Type Details
    Route::post('/', [RateTypeController::class, 'store']);// Create Rate Type
    Route::put('/{id}', [RateTypeController::class, 'update']);// Update Rate Type
    Route::delete('/{id}', [RateTypeController::class, 'destroy']);// Delete Rate Type
    Route::post('/{typeId}/rates/{rateId}', [RateTypeController::class, 'linkRateToType']);// Link Rate Type to Rate
    Route::get('/{typeId}/rates', [RateTypeController::class, 'listRatesForType']);// List Rates for a Rate Type
    Route::get('/properties/{propertyId}', [RateTypeController::class, 'listByProperty']);// List Rate Types by Property
});


/**
 * HousekeepingLog API Routes
 */
// Grouping HousekeepingLog routes under '/housekeeping-logs' prefix
Route::prefix('housekeeping-logs')->group(function () {
    Route::get('/', [HousekeepingLogController::class, 'index']);// List Housekeeping Logs
    Route::get('/{id}', [HousekeepingLogController::class, 'show']);// Get Housekeeping Log Details
    Route::post('/', [HousekeepingLogController::class, 'store']); // Create Housekeeping Log
    Route::put('/{id}', [HousekeepingLogController::class, 'update']);// Update Housekeeping Log
    Route::delete('/{id}', [HousekeepingLogController::class, 'destroy']);// Delete Housekeeping Log
    Route::post('/{id}/assign', [HousekeepingLogController::class, 'assignTask']);// Assign Housekeeping Task
    Route::get('/properties/{propertyId}', [HousekeepingLogController::class, 'listLogsByProperty']);// List Housekeeping Logs by Property
});


/**
 * Maintenance API Routes
 */
// Grouping MaintenanceRequest routes under '/maintenance-requests' prefix
Route::prefix('maintenance-requests')->group(function () {
    Route::get('/', [MaintenanceRequestController::class, 'index']);// List Maintenance Requests
    Route::get('/{id}', [MaintenanceRequestController::class, 'show']);// Get Maintenance Request Details
    Route::post('/', [MaintenanceRequestController::class, 'store']);// Create Maintenance Request
    Route::put('/{id}', [MaintenanceRequestController::class, 'update']);// Update Maintenance Request
    Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy']);// Delete Maintenance Request
    Route::post('/{id}/assign', [MaintenanceRequestController::class, 'assignTask']);// Assign Maintenance Task
    Route::put('/{id}/status', [MaintenanceRequestController::class, 'updateStatus']);// Update Maintenance Task Status
    Route::get('/properties/{propertyId}', [MaintenanceRequestController::class, 'listRequestsByProperty']);// List Maintenance Requests by Property
});


/**
 * Group API Routes
 */
// Grouping Group routes under '/groups' prefix
Route::prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);// List Groups
    Route::get('/{id}', [GroupController::class, 'show']);// Get Group Details
    Route::post('/', [GroupController::class, 'store']); // Create Group Booking or Event
    Route::put('/{id}', [GroupController::class, 'update']); // Update Group Booking
    Route::delete('/{id}', [GroupController::class, 'destroy']); // Delete Group Booking
    Route::get('/{groupId}/reservations', [GroupController::class, 'listReservationsForGroup']);// List Reservations for a Group
    Route::post('/{id}/special-requests', [GroupController::class, 'handleSpecialRequests']);// Handle Special Requests for Groups
    Route::post('/{id}/allocate-rooms', [GroupController::class, 'allocateRooms']);// Allocate Rooms for Groups
});



/**
 * Allotments API Routes
 */
// Grouping Allotment routes under '/allotments' prefix
Route::prefix('allotments')->group(function () {
    Route::get('/', [AllotmentController::class, 'index']);// List Allotments
    Route::get('/{id}', [AllotmentController::class, 'show']);// Get Allotment Details
    Route::post('/', [AllotmentController::class, 'store']);// Create Allotment
    Route::put('/{id}', [AllotmentController::class, 'update']);// Update Allotment
    Route::delete('/{id}', [AllotmentController::class, 'destroy']);// Delete Allotment
    Route::get('/properties/{propertyId}', [AllotmentController::class, 'listAllotmentsByProperty']);// List Allotments by Property
    Route::put('/{id}/reallocate', [AllotmentController::class, 'reallocateRooms']);// Reallocate Rooms within an Allotment
});


/**
 * Promotion API Routes
 */
// Grouping Promotion routes under '/promotions' prefix
Route::prefix('promotions')->group(function () {
    Route::get('/', [PromotionController::class, 'index']);// List Promotions
    Route::get('/{id}', [PromotionController::class, 'show']);// Get Promotion Details
    Route::post('/', [PromotionController::class, 'store']);// Create Promotion
    Route::put('/{id}', [PromotionController::class, 'update']);// Update Promotion
    Route::delete('/{id}', [PromotionController::class, 'destroy']);// Delete Promotion
    Route::post('/{promotionId}/rates/{rateId}', [PromotionController::class, 'linkToRate']);// Link Promotion to Rate
    Route::put('/{id}/toggle-activation', [PromotionController::class, 'toggleActivation']);// Activate/Deactivate Promotion
    Route::get('/properties/{propertyId}', [PromotionController::class, 'listByProperty']);// List Promotions by Property
});



/**
 * Discount API Routes
 */
// Grouping Discount routes under '/discounts' prefix
Route::prefix('discounts')->group(function () {
    Route::get('/', [DiscountController::class, 'index']);// List Discounts
    Route::get('/{id}', [DiscountController::class, 'show']);// Get Discount Details
    Route::post('/', [DiscountController::class, 'store']);// Create Discount
    Route::put('/{id}', [DiscountController::class, 'update']); // Update Discount
    Route::delete('/{id}', [DiscountController::class, 'destroy']);// Delete Discount
    Route::post('/reservations/{reservationId}/{discountId}', [DiscountController::class, 'applyToReservation']);// Apply Discount to Reservation
    Route::get('/properties/{propertyId}', [DiscountController::class, 'listByProperty']);// List Discounts by Property
    Route::put('/{id}/toggle-activation', [DiscountController::class, 'toggleActivation']);// Activate/Deactivate Discount
});



/**
 * Feedback API Routes
 */
// Grouping Feedback routes under '/feedback' prefix
Route::prefix('feedback')->group(function () {
    Route::get('/', [FeedbackController::class, 'index']);// List Feedback Entries
    Route::get('/{id}', [FeedbackController::class, 'show']);// Get Feedback Details
    Route::post('/', [FeedbackController::class, 'store']);// Create Feedback Entry
    Route::put('/{id}', [FeedbackController::class, 'update']);// Update Feedback Entry
    Route::delete('/{id}', [FeedbackController::class, 'destroy']);// Delete Feedback Entry
    Route::get('/guests/{guestId}', [FeedbackController::class, 'listFeedbackByGuest']);// List Feedback by Guest
    Route::get('/properties/{propertyId}', [FeedbackController::class, 'listFeedbackByProperty']);// List Feedback by Property
    Route::get('/aggregate', [FeedbackController::class, 'aggregateFeedback']);// Aggregate Feedback for Analysis
});


/**
 * GuestPrefrence API Routes
 */
// Grouping GuestPreference routes under '/guest-preferences' prefix
Route::prefix('guest-preferences')->group(function () {
    Route::get('/', [GuestPreferenceController::class, 'index']);// List Guest Preferences
    Route::get('/{id}', [GuestPreferenceController::class, 'show']);// Get Guest Preference Details
    Route::post('/', [GuestPreferenceController::class, 'store']);// Create Guest Preference
    Route::put('/{id}', [GuestPreferenceController::class, 'update']);// Update Guest Preference
    Route::delete('/{id}', [GuestPreferenceController::class, 'destroy']);// Delete Guest Preference
    Route::get('/guests/{guestId}', [GuestPreferenceController::class, 'listPreferencesByGuest']);// List Preferences by Guest
    Route::get('/{id}/match-room', [GuestPreferenceController::class, 'matchRoomWithPreferences']);// Match Room with Guest Preferences
});


/**
 * LoyaltyProgramme API Routes
 */
// Grouping LoyaltyProgram routes under '/loyalty-programs' prefix
Route::prefix('loyalty-programs')->group(function () {
    Route::get('/', [LoyaltyProgramController::class, 'index']); // List Loyalty Programs
    Route::get('/{id}', [LoyaltyProgramController::class, 'show']);// Get Loyalty Program Details
    Route::post('/', [LoyaltyProgramController::class, 'store']);// Create Loyalty Program
    Route::put('/{id}', [LoyaltyProgramController::class, 'update']);// Update Loyalty Program
    Route::delete('/{id}', [LoyaltyProgramController::class, 'destroy']);// Delete Loyalty Program
    Route::post('/guests/{guestId}/{programId}/enroll', [LoyaltyProgramController::class, 'enrollGuest']);// Enroll Guest in Loyalty Program
    Route::put('/guests/{guestId}/{programId}/points', [LoyaltyProgramController::class, 'updateGuestPoints']);// Update Loyalty Points for Guest
    Route::get('/{programId}/members', [LoyaltyProgramController::class, 'listProgramMembers']);// List Loyalty Program Members
});



/**
 * InventoryItems API Routes
 */
// Grouping InventoryItem routes under '/inventory-items' prefix
Route::prefix('inventory-items')->group(function () {
    Route::get('/', [InventoryItemController::class, 'index']);// List Inventory Items
    Route::get('/{id}', [InventoryItemController::class, 'show']);// Get Inventory Item Details
    Route::post('/', [InventoryItemController::class, 'store']);// Create Inventory Item
    Route::put('/{id}', [InventoryItemController::class, 'update']);// Update Inventory Item
    Route::delete('/{id}', [InventoryItemController::class, 'destroy']);// Delete Inventory Item
    Route::get('/properties/{propertyId}', [InventoryItemController::class, 'listItemsByProperty']);// List Inventory Items by Property
    Route::get('/check-levels', [InventoryItemController::class, 'checkInventoryLevels']);// Check Inventory Levels
    Route::post('/{id}/reorder', [InventoryItemController::class, 'reorderItem']);// Reorder Inventory Item
});


/**
 * PurchaseOrder API Routes
 */
// Grouping PurchaseOrder routes under '/purchase-orders' prefix
Route::prefix('purchase-orders')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index']);// List Purchase Orders
    Route::get('/{id}', [PurchaseOrderController::class, 'show']);// Get Purchase Order Details
    Route::post('/', [PurchaseOrderController::class, 'store']);// Create Purchase Order
    Route::put('/{id}', [PurchaseOrderController::class, 'update']);// Update Purchase Order
    Route::delete('/{id}', [PurchaseOrderController::class, 'destroy']);// Delete Purchase Order
    Route::put('/{id}/approval', [PurchaseOrderController::class, 'approveOrReject']);// Approve or Reject Purchase Order
    Route::get('/{id}/tracking', [PurchaseOrderController::class, 'trackDelivery']);// Track Delivery of Purchase Order
    Route::get('/suppliers/{supplierId}', [PurchaseOrderController::class, 'listBySupplier']);// List Purchase Orders by Supplier
});


/**
 * Suppliers API Routes
 */
// Grouping Supplier routes under '/suppliers' prefix
Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index']);// List Suppliers
    Route::get('/{id}', [SupplierController::class, 'show']);// Get Supplier Details
    Route::post('/', [SupplierController::class, 'store']);// Create Supplier
    Route::put('/{id}', [SupplierController::class, 'update']);// Update Supplier
    Route::delete('/{id}', [SupplierController::class, 'destroy']);// Delete Supplier
    Route::get('/{supplierId}/orders', [SupplierController::class, 'listSupplierOrders']);// List Supplier Orders
    Route::post('/{supplierId}/orders', [SupplierController::class, 'manageOrders']);// Manage Supplier Orders
    Route::get('/properties/{propertyId}', [SupplierController::class, 'listSuppliersByProperty']);// List Suppliers by Property
});




/**
 * Report API Routes
 * Generate Specific Reports
 */
// Grouping Report routes under '/reports' prefix
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index']);// List Reports
    Route::get('/{id}', [ReportController::class, 'show']);// Get Report Details
    Route::post('/', [ReportController::class, 'store']);// Create Report
    Route::put('/{id}', [ReportController::class, 'update']); // Update Report
    Route::delete('/{id}', [ReportController::class, 'destroy']);// Delete Report
    Route::get('/occupancy', [ReportController::class, 'generateOccupancyReport']);// Generate Occupancy Report
    Route::get('/revenue', [ReportController::class, 'generateRevenueReport']);// Generate Revenue Report
    Route::get('/feedback', [ReportController::class, 'aggregateFeedback']);// Aggregate Guest Feedback
});




/**
 * Analytic API Routes
 * Specific Analytics Reports
 */
// Grouping Analytic routes under '/analytics' prefix
Route::prefix('analytics')->group(function () {
    Route::get('/', [AnalyticController::class, 'index']);// List Analytics
    Route::get('/{id}', [AnalyticController::class, 'show']);// Get Analytics Report Details
    Route::post('/', [AnalyticController::class, 'store']);// Create Analytics Report
    Route::put('/{id}', [AnalyticController::class, 'update']);// Update Analytics Report
    Route::delete('/{id}', [AnalyticController::class, 'destroy']);// Delete Analytics Report
    Route::get('/guest-satisfaction', [AnalyticController::class, 'guestSatisfactionAnalysis']);// Guest Satisfaction Analytics
    Route::get('/revenue', [AnalyticController::class, 'revenueAnalysis']);// Revenue Analytics
    Route::get('/occupancy-trends', [AnalyticController::class, 'occupancyTrendAnalysis']);// Occupancy Trend Analysis
});


/**
 * Message API Routes
 */
// Grouping Message routes under '/messages' prefix
Route::prefix('messages')->group(function () {
    Route::get('/', [MessageController::class, 'index']);// List Messages
    Route::get('/{id}', [MessageController::class, 'show']);// Get Message Details
    Route::post('/', [MessageController::class, 'store']);// Send Message
    Route::put('/{id}', [MessageController::class, 'update']);// Update Message
    Route::delete('/{id}', [MessageController::class, 'destroy']);// Delete Message
    Route::get('/sender/{senderId}', [MessageController::class, 'listBySender']);// List Messages by Sender
    Route::get('/receiver/{receiverId}', [MessageController::class, 'listByReceiver']);// List Messages by Receiver
    Route::get('/unread', [MessageController::class, 'fetchUnread']);// Fetch Unread Messages
});


/**
 * PaymentGateway API Routes
 */
// Grouping PaymentGateway routes under '/payment-gateways' prefix
Route::prefix('payment-gateways')->group(function () {
    Route::get('/', [PaymentGatewayController::class, 'index']);// List Payment Gateways
    Route::get('/{id}', [PaymentGatewayController::class, 'show']);// Get Payment Gateway Details
    Route::post('/', [PaymentGatewayController::class, 'store']);// Add Payment Gateway
    Route::put('/{id}', [PaymentGatewayController::class, 'update']);// Update Payment Gateway
    Route::delete('/{id}', [PaymentGatewayController::class, 'destroy']);// Remove Payment Gateway
    Route::post('/{id}/process-payment', [PaymentGatewayController::class, 'processPayment']);// Process Payment
    Route::get('/{gatewayId}/transactions', [PaymentGatewayController::class, 'listTransactionsByGateway']);// List Transactions by Gateway
    Route::put('/{id}/settings', [PaymentGatewayController::class, 'updateSettings']);// Update Gateway Settings
});



/**
 * Tax API Routes
 */
// Grouping Tax routes with common middleware
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('taxes')->group(function() {
        Route::get('/', [TaxController::class, 'index'])->name('taxes.index');// List all taxes
        Route::get('/{taxId}', [TaxController::class, 'show'])->name('taxes.show'); // Get details of a specific tax
        Route::post('/', [TaxController::class, 'store'])->name('taxes.store'); // Create a new tax
        Route::put('/{taxId}', [TaxController::class, 'update'])->name('taxes.update'); // Update an existing tax
        Route::delete('/{taxId}', [TaxController::class, 'destroy'])->name('taxes.destroy');// Delete a tax
        Route::post('/{taxId}/toggle-activation', [TaxController::class, 'toggleTaxActivation'])->name('taxes.toggleActivation'); // Toggle the activation status of a tax
        Route::post('/{taxId}/adjust-rate', [TaxController::class, 'adjustTaxRate'])->name('taxes.adjustRate'); // Adjust a tax rate
        Route::post('/{taxId}/apply', [TaxController::class, 'applyTax'])->name('taxes.applyTax');// Apply a tax to a specific reservation or folio
        Route::get('/service/{serviceId}', [TaxController::class, 'listApplicableTaxesForService'])->name('taxes.listForService'); // List taxes applicable to a specific service
    });
});



/**
 * Channels API Routes
 */
// Grouping Channel routes under '/channels' prefix
Route::prefix('channels')->group(function () {
    Route::get('/', [ChannelController::class, 'index']);// List Channels
    Route::get('/{id}', [ChannelController::class, 'show']);// Get Channel Details
    Route::post('/', [ChannelController::class, 'store']);// Create Channel
    Route::put('/{id}', [ChannelController::class, 'update']);// Update Channel
    Route::delete('/{id}', [ChannelController::class, 'destroy']); // Delete Channel
    Route::post('/{id}/synchronize', [ChannelController::class, 'synchronizeData']);// Synchronize Channel Data
    Route::get('/{id}/performance', [ChannelController::class, 'analyzePerformance']);// Analyze Channel Performance
    Route::get('/{channelId}/reservations', [ChannelController::class, 'listReservationsByChannel']);// List Reservations by Channel
});


/**
 * RoomAllocation API Routes
 */
// Grouping RoomAllocation routes under '/room-allocations' prefix
Route::prefix('room-allocations')->group(function () {
    Route::get('/', [RoomAllocationController::class, 'index']);// List Room Allocations
    Route::get('/{id}', [RoomAllocationController::class, 'show']); // Get Room Allocation Details
    Route::post('/', [RoomAllocationController::class, 'store']);// Create Room Allocation
    Route::put('/{id}', [RoomAllocationController::class, 'update']);// Update Room Allocation
    Route::delete('/{id}', [RoomAllocationController::class, 'destroy']);// Delete Room Allocation
    Route::get('/reservations/{reservationId}', [RoomAllocationController::class, 'listAllocationsByReservation']);// List Allocations by Reservation
    Route::get('/properties/{propertyId}', [RoomAllocationController::class, 'listAllocationsByProperty']);// List Allocations by Property
    Route::put('/{id}/change', [RoomAllocationController::class, 'changeAllocation']);// Change Room Allocation
});

