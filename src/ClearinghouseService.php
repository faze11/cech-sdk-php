<?php

namespace App\Services\Clearinghouse;

use App\Activity;
use App\Business;
use Carbon\Carbon;
use App\PhoneNumber;
use App\Billing\Payer;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceItem;
use App\Services\Clearinghouse\Data\Adl;
use App\Services\Clearinghouse\Data\Visit;
use App\Claims\Contracts\ClaimableInterface;
use App\Services\Clearinghouse\Data\Address;
use App\Services\Clearinghouse\Data\AdlCode;
use http\Exception\InvalidArgumentException;
use App\Services\Clearinghouse\Data\Claimant;
use App\Services\Clearinghouse\Data\UnitType;
use App\Services\Clearinghouse\Data\Caregiver;
use App\Services\Clearinghouse\Data\PhoneType;
use App\Services\Clearinghouse\Data\AddressType;
use App\Services\Clearinghouse\Data\Coordinates;
use App\Services\Clearinghouse\Data\ServiceCode;
use App\Services\Clearinghouse\Data\RateIndicator;
use App\Services\Clearinghouse\Data\VisitLineItem;
use App\Services\Clearinghouse\Data\ClaimSubmission;
use App\Services\Clearinghouse\Data\CaregiverLicense;
use App\Services\Clearinghouse\Data\UnverifiedReason;
use App\Services\Clearinghouse\Data\VerificationMethod;
use App\Services\Clearinghouse\Data\SubmissionStatusResponse;

class ClearinghouseService
{
    public ApiClient $client;

    public function __construct(ApiClientConfig $config)
    {
        $this->client = new ApiClient($config);
    }

    /**
     * Submit a Claim and return the submission ID.
     *
     * @param ClaimSubmission $claim
     * @return string|null
     * @throws \Exception
     */
    public function submitClaim(ClaimSubmission $claim): string
    {
        try {
            return $this->client->submitClaim($claim);
        } catch (\Exception $ex) {
            \Log::error('[ClearinghouseService.submitClaim] Exception: ' . $ex->getMessage());
            // TODO: handle errors and throw predictable Clearinghouse exception
            throw $ex;
        }
    }

    public function getSubmissionStatus(string $submissionId): SubmissionStatusResponse
    {
        try {
            return $this->client->getStatus($submissionId);
        } catch (\Exception $ex) {
            \Log::error('[ClearinghouseService.getSubmissionStatus] Exception: ' . $ex->getMessage());
            // TODO: handle errors and throw predictable Clearinghouse exception
            throw $ex;
        }
    }

    /**
     * Activate a Provider with Ally.
     *
     * @param string $providerId
     * @return string|null
     * @throws \Exception
     */
    public function activateProvider(string $providerId): string
    {
        try {
            return $this->client->activateProvider($providerId);
        } catch (\Exception $ex) {
            \Log::error('[ClearinghouseService.activateProvider] Exception: ' . $ex->getMessage());
            // TODO: handle errors and throw predictable Clearinghouse exception
            throw $ex;
        }
    }

    /**
     * Get a ClaimSubmission object from a ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @return ClaimSubmission
     */
    public function getClaimData(ClaimInvoice $claim): ClaimSubmission
    {
        $claim->load(['client', 'payer', 'items']);

        return new ClaimSubmission([
            'reference_id'        => (string) $claim->name,
            'provider_id'         => $claim->business->cech_id,
            'provider_name'       => $claim->business->name,
            'provider_npi'        => $this->getProviderNpi($claim->business, $claim->payer),
            'provider_tax_id'     => $claim->business->ein,
            'provider_address'    => $this->getAddress($claim->business),
            'provider_phone'      => $this->convertPhone($claim->business->phone1),
            'provider_phone_type' => PhoneType::LANDLINE(), // TODO: Do not have
            'carrier_code'        => $claim->payer_code ? $claim->payer_code : $claim->payer->payer_code,
            'claimant'            => $this->getClaimant($claim),
            'timezone'            => $claim->business->timezone,
            'date'                => $claim->getDate()->format('Y-m-d'),
            'revision_number'     => 0, // TODO: support re-transmitting as revisions
            'original_claim_id'   => null, // TODO: support re-transmitting as revisions
            'amount'              => $claim->getAmount(),
            'visits'              => $this->getVisits($claim),
            'fees'                => [],
        ]);
    }

    /**
     * Get a list of Visit objects for a Claim invoice.
     *
     * @param ClaimInvoice $claim
     * @return array
     */
    public function getVisits(ClaimInvoice $claim): array
    {
        $visits = [];

        /** @var ClaimInvoiceItem $item */
        foreach ($claim->serviceItems as $item) {
            /** @var ClaimableService $service */
            $service = $item->claimable;

            $shiftId = $this->getVisitReference($service);
            $timezone = $claim->business->timezone; // TODO: use client timezone
            $visitLineItem = $this->getVisitLineItem($item, $timezone);

            if (! isset($visits[$shiftId])) {
                $visits[$shiftId] = new Visit([
                    'reference_id'                       => $shiftId,
                    'caregiver'                          => $this->getCaregiver($item),
                    'timezone'                           => $timezone,
                    'start_time_scheduled'               => $this->convertDateTime($service->scheduled_start_time, $timezone),
                    'start_time_recorded'                => $this->convertDateTime($service->evv_start_time ?? $service->visit_start_time, $timezone),
                    'start_time_invoiced'                => $this->convertDateTime($service->visit_start_time, $timezone),
                    'start_address_scheduled'            => $this->getAddress($service), // TODO: DO NOT HAVE
                    'start_address_type_scheduled'       => AddressType::HOME(), // TODO: DO NOT HAVE
                    'start_address_type_other_scheduled' => null,
                    'start_address_invoiced'             => $this->getAddress($service),
                    'start_address_type_invoiced'        => AddressType::HOME(), // TODO: DO NOT HAVE
                    'start_address_type_other_invoiced'  => null,
                    'start_verification_method'          => $this->convertVerificationMethod($service->evv_method_in),
                    'start_address_coordinates_recorded' => $this->convertCoordinates($service->checked_in_latitude, $service->checked_in_longitude),
                    'start_user_agent'                   => $service->shift->checked_in_agent, // TODO: Should come from claim
                    'start_ip_address'                   => $service->shift->checked_in_ip, // TODO: Should come from claim
                    'start_phone_number'                 => $this->convertPhone($service->checked_in_number),
                    'start_unverified_reason_code'       => $this->getUnverifiedReason($service),
                    'end_time_scheduled'                 => $this->convertDateTime($service->scheduled_end_time, $timezone),
                    'end_time_recorded'                  => $this->convertDateTime($service->evv_end_time ?? $service->visit_end_time, $timezone),
                    'end_time_invoiced'                  => $this->convertDateTime($service->visit_end_time, $timezone),
                    'end_address_scheduled'              => $this->getAddress($service), // TODO: DO NOT HAVE
                    'end_address_type_scheduled'         => AddressType::HOME(), // TODO: DO NOT HAVE
                    'end_address_type_other_scheduled'   => null,
                    'end_address_invoiced'               => $this->getAddress($service),
                    'end_address_type_invoiced'          => AddressType::HOME(), // TODO: DO NOT HAVE
                    'end_address_type_other_invoiced'    => null,
                    'end_verification_method'            => $this->convertVerificationMethod($service->evv_method_out),
                    'end_address_coordinates_recorded'   => $this->convertCoordinates($service->checked_out_latitude, $service->checked_out_longitude),
                    'end_user_agent'                     => $service->shift->checked_out_agent, // TODO: Should come from claim
                    'end_ip_address'                     => $service->shift->checked_out_ip, // TODO: Should come from claim
                    'end_phone_number'                   => $this->convertPhone($service->checked_out_number),
                    'end_unverified_reason_code'         => $this->getUnverifiedReason($service),
                    'adls'                               => $this->getAdls($service, $claim->business_id),
                    'notes'                              => $service->caregiver_comments,
                    'line_items'                         => [$visitLineItem],
                    'amount'                             => 0, // sum at the end
                ]);
            } else {
                // append line item to existing visit record
                array_push($visits[$shiftId]->line_items, $visitLineItem);
            }
        }

        // Add expenses after to ensure visit data exists

        foreach ($claim->expenseItems as $item) {
            /** @var ClaimableExpense $service */
            $service = $item->claimable;
            $shiftId = $this->getVisitReference($service);
            $visitLineItem = $this->getVisitLineItem($item, $timezone);

            if (! isset($visits[$shiftId])) {
                // Visit entry does not exist on the Claim
                // TODO: This can be implemented by adding to fee line items outside of the visit
            }
            array_push($visits[$shiftId]->line_items, $visitLineItem);
        }

        // Add visit amounts together
        $visits = collect($visits)->map(function (Visit $visit) {
            $visit->amount = collect($visit->line_items)->bcsum('amount');

            return $visit;
        });

        return $visits->values()->toArray();
    }

    /**
     * Get a VisitLineItem object from a ClaimInvoiceItem.
     *
     * @param ClaimInvoiceItem $item
     * @return VisitLineItem
     */
    public function getVisitLineItem(ClaimInvoiceItem $item, string $timezone): VisitLineItem
    {
        list($serviceCode, $unitType) = $this->getServiceCode($item);

        return new VisitLineItem([
            'reference_id'        => (string) $item->claimable_id,
            'service_code'        => $serviceCode->getValue(),
            'service_code_other'  => null,
            'rate'                => (float) $item->rate,
            'rate_indicator'      => RateIndicator::REG(), // TODO: support hours_type
            'unit_type'           => $unitType,
            'units'               => (float) $item->units,
            'amount'              => (float) $item->amount,
            'start_time_invoiced' => $item->claimable->visit_start_time ? $this->convertDateTime($item->claimable->visit_start_time, $timezone) : null,
            'end_time_invoiced'   => $item->claimable->visit_end_time ? $this->convertDateTime($item->claimable->visit_end_time, $timezone) : null,
        ]);
    }

    /**
     * Get Claimant object from a ClaimInvoice.
     *
     * @param ClaimInvoice $claim
     * @return Claimant
     */
    public function getClaimant(ClaimInvoice $claim): Claimant
    {
        $client = $claim->client;
        /** @var ClaimInvoiceItem $item */
        $item = $claim->items->first();

        $phone = $item->client->getPhoneNumber();  // TODO: Should come from claim
        $phoneType = optional($phone)->type == PhoneNumber::TYPE_MOBILE ? PhoneType::MOBILE() : PhoneType::LANDLINE();

        return new Claimant([
            'policy_number'             => $item->client_ltci_policy_number,
            'medicaid_id'               => $item->client_medicaid_id,
            'dob'                       => $item->client_dob,
            'first_name'                => $item->client_first_name,
            'middle_name'               => $client->middle_name, // TODO: Should come from claim
            'last_name'                 => $item->client_last_name,
            'name_suffix'               => null, // TODO: DO NOT HAVE
            'mailing_address'           => $this->getAddress($client->billingAddress ?? $client->evvAddress), // TODO: Should come from claim
            'service_address'           => $this->getAddress($client->evvAddress), // TODO: Should come from claim
            'service_address_alternate' => null, // TODO: DO NOT HAVE
            'contact_phone'             => $this->convertPhone($phone),
            'contact_phone_type'        => $phone ? $phoneType : null,
            'email'                     => $client->hasNoEmail() ? null : $client->email, // TODO: Should come from claim
        ]);
    }

    /**
     * Get a Caregiver object from a ClaimInvoiceItem.
     *
     * @param ClaimInvoiceItem $item
     * @return Caregiver
     */
    public function getCaregiver(ClaimInvoiceItem $item): Caregiver
    {
        $caregiver = $item->caregiver;

        $phone = $caregiver->getPhoneNumber();  // TODO: Should come from claim
        $phoneType = optional($phone)->type == PhoneNumber::TYPE_MOBILE ? PhoneType::MOBILE() : PhoneType::LANDLINE();

        return new Caregiver([
            'reference_id'       => (string) $caregiver->id,
            'dob'                => $item->caregiver_dob,
            'ssn'                => $item->caregiver_ssn,
            'license_type'       => $this->convertLicenseCode($caregiver->certification)->getValue(), // TODO: Should come from claim
            'first_name'         => $item->caregiver_first_name,
            'middle_name'        => $caregiver->middle_name, // TODO: Should come from claim
            'last_name'          => $item->caregiver_last_name,
            'contact_phone'      => $this->convertPhone($phone),
            'contact_phone_type' => $phone ? $phoneType : null,
            'email'              => $caregiver->hasNoEmail() ? null : $caregiver->email, // TODO: Should come from claim
            'address'            => $this->getAddress($caregiver->address), // TODO: Should come from claim
        ]);
    }

    /**
     * Get the Provider's NPI number.
     *
     * @param Business $business
     * @param Payer $payer
     * @return string
     */
    public function getProviderNpi(Business $business, Payer $payer): ?string
    {
        if (empty($payer->npi_number)) {
            return $business->medicaid_npi_number;
        }

        return $payer->npi_number;
    }

    /**
     * Get the CECH service code for a given Claim item.
     * Returns a tuple of (service code, unit type)
     *
     * @param ClaimInvoiceItem $item
     * @return array
     */
    public function getServiceCode(ClaimInvoiceItem $item): array
    {
        switch ($item->claimable_type) {
            case ClaimableService::class:
                // TODO: Implement new CH field for service code
                // TODO: implement DAY unit type for fixed rate shifts
                return [ServiceCode::PERSONAL_CARE(), UnitType::HOUR()];

            case ClaimableExpense::class:
                /** @var ClaimableExpense $expense */
                $expense = $item->claimable;

                if (empty($expense)) {
                    return [ServiceCode::EXPENSES(), UnitType::DAY()];
                }

                if (strtoupper($expense->name) == 'MILEAGE') {
                    return [ServiceCode::MILEAGE(), UnitType::MILE()];
                }

                return [ServiceCode::EXPENSES(), UnitType::DAY()];
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Get all ADls from a ClaimableService.
     *
     * Requires business ID to support getting custom activities.
     *
     * @param ClaimableService $service
     * @param int $businessId
     * @return array|null
     */
    public function getAdls(ClaimableService $service, int $businessId): ?array
    {
        return collect($service->getActivities($businessId))
            ->map(function (Activity $activity) {
                return $this->convertAdl($activity);
            })
            ->toArray();
    }

    /**
     * Convert an Ally Activity class to a Clearinghouse Adl class.
     *
     * @param Activity $activity
     * @return Adl
     */
    public function convertAdl(Activity $activity): Adl
    {
        switch ($activity->code) {
            case '001': // Bathing - Shower
                return new Adl(['code' => AdlCode::BATHING()->getValue()]);
            case '002': // Bathing - Bed
                return new Adl(['code' => AdlCode::BATHING()->getValue()]);
            case '003': // Dressing
                return new Adl(['code' => AdlCode::DRESSING()->getValue()]);
            case '024': // Feeding
                return new Adl(['code' => AdlCode::EATING()->getValue()]);
            case '008': // Toileting
                return new Adl(['code' => AdlCode::TOILETING()->getValue()]);
            case '007': // Incontinence Care
                return new Adl(['code' => AdlCode::CONTINENCE()->getValue()]);
            case '027': // Ambulation
                return new Adl(['code' => AdlCode::AMBULATION_MOBILITY()->getValue()]);
            case '021': // Medication Reminders
                return new Adl(['code' => AdlCode::MEDICATION_ADMINISTRATION()->getValue()]);
            case '023': // Meal Preparation
                return new Adl(['code' => AdlCode::MEAL_PREPARATION()->getValue()]);
            case '025': // Homemaker Services
                return new Adl(['code' => AdlCode::HOUSEKEEPING()->getValue()]);
            case '026': // Transportation
                return new Adl(['code' => AdlCode::TRANSPORTATION()->getValue()]);

            case '005': // Hygiene - Hair Care
            case '006': // Shave
            case '004': // Hygiene - Mouth Care
            case '020': // Turning & Repositioning
            case '022': // Safety Supervision
            case '009': // Catheter Care
            case '010': // Ostomy Care
            case '011': // Companion Care
            case '028': // Wound Care
            case '029': // Respite Care (Skilled Nursing)
            case '030': // Respite Care (General)
            default:
                return new Adl(
                    [
                    'code'          => AdlCode::OTHER()->getValue(),
                    'other_comment' => $activity->name]
                );
        }
    }

    /**
     * Get the visit reference ID from a Claimable Service/Expense.
     *
     * @param ClaimableInterface $claimable
     * @return string
     */
    public function getVisitReference(ClaimableInterface $claimable): string
    {
        $shiftId = $claimable->getShiftId();

        if (empty($shiftId)) {
            // TODO: Figure out how to support sending manually added claim items (with no shift ref)
            throw new InvalidArgumentException();
        }

        return (string) $shiftId;
    }

    /**
     * Get the unverified reason for a ClaimableService, if exists.
     *
     * @param ClaimableService $service
     * @return UnverifiedReason|null
     */
    public function getUnverifiedReason(ClaimableService $service): ?UnverifiedReason
    {
        // TODO: Add support for missing unverified reasons as well as a reason per clock in/out.

        if ($service->visit_edit_action_id && $service->visit_edit_reason_id) {
            return null;
        }

        switch ($service->visit_edit_reason_id) {
            case 105: //    105,Services Provided Outside the Home – Supported by Service Plan
                return UnverifiedReason::SERVICE_OUTSIDE_HOME();
            case 110: //    110,Fill-in for Regular Attendant or Assigned Staff
                return UnverifiedReason::FILL_IN();
            case 115: //    115,Client requested to change/cancel scheduled visit Scheduled visit has been cancelled due to the client's services being suspended
                return UnverifiedReason::CLIENT_CANCELLED();
            case 120: //    120,Attendant's identification number (s) does not match the scheduled shift
                return UnverifiedReason::CAREGIVER_ID_MISMATCH();
            case 130:  //    130,Disaster or Emergency
                return UnverifiedReason::DISASTER_EMERGENCY();
            case 300: //    300,Client's phone line not working (technical issue or natural disaster)
                return UnverifiedReason::CLIENT_PHONE_NOT_WORKING();
            case 305: //    305,Attendant unable to connect to internet or EVV system down; Attendant entered invalid fixed location device code(s)
                return UnverifiedReason::CAREGIVER_NO_SERVICE();
            case 310: //    310,Attendant unable to use mobile device
                return UnverifiedReason::CAREGIVER_TECHNICAL_DIFFICULTIES_EVV();
            case 405: //    405,Phone in use by client or individual in client's home
                return UnverifiedReason::CLIENT_PHONE_IN_USE();
            case 410: //    410,Client won't let attendant use phone
                return UnverifiedReason::CLIENT_PHONE_IN_USE();
            case 915: //    915,Wrong Phone Number – Verified Services Were Delivered
                return UnverifiedReason::CAREGIVER_WRONG_PHONE_TVV();
            default:
                return null;
        }
    }

    /**
     * Convert lat/lon to Coordinates object.
     *
     * @param float|null $latitude
     * @param float|null $longitude
     * @return Coordinates|null
     */
    public function convertCoordinates(?float $latitude, ?float $longitude): ?Coordinates
    {
        if (empty($latitude) || empty($longitude)) {
            return null;
        }

        return new Coordinates([
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'elevation' => null,
        ]);
    }

    /**
     * Convert verification method.
     *
     * @param string|null $evv_method
     * @return VerificationMethod
     */
    public function convertVerificationMethod(?string $evv_method): VerificationMethod
    {
        switch ($evv_method) {
            case ClaimableService::EVV_METHOD_TELEPHONY:
                return VerificationMethod::TVV();
            case ClaimableService::EVV_METHOD_GEOLOCATION:
                return VerificationMethod::EVV();
            default:
                return VerificationMethod::NONE();
        }
    }

    /**
     * Convert Caregiver license string to a CaregiverLicenseCode.
     *
     * @param string|null $certification
     * @return CaregiverLicense|null
     */
    public function convertLicenseCode(?string $certification): ?CaregiverLicense
    {
        switch ($certification) {
            case 'CNA':
                return CaregiverLicense::CNA();
            case 'HCA':
                return CaregiverLicense::HOME_CARE_AIDE();
            case 'HHA':
                return CaregiverLicense::HHA();
            case 'LPN':
                return CaregiverLicense::LPN();
            case 'RN':
                return CaregiverLicense::RN();
            default:
                return CaregiverLicense::UNLICENSED_CAREGIVER();
        }
    }

    /**
     * Create an Address object from a mixture of entities.
     *
     * Supports Business, Address, and ClaimableService models.
     *
     * @param \App\Address|\App\Business|\App\Claims\ClaimableService|null $entity
     * @return Address|null
     */
    public function getAddress($entity): ?Address
    {
        if (empty($entity)) {
            return null;
        }

        if ($entity instanceof Business) {
            return new Address([
                'address1' => $entity->address1,
                'address2' => $entity->address2,
                'city'     => $entity->city,
                'state'    => $entity->state,  // TODO: always convert state to abbreviations?
                'zip'      => $entity->zip,
                'country'  => $entity->country,
            ]);
        } elseif ($entity instanceof \App\Address) {
            return new Address([
                'address1' => $entity->address1,
                'address2' => $entity->address2,
                'city'     => $entity->city,
                'state'    => $entity->state, // TODO: always convert state to abbreviations?
                'zip'      => $entity->zip,
                'country'  => $entity->country,
            ]);
        } elseif ($entity instanceof ClaimableService) {
            return new Address([
                'address1' => $entity->address1,
                'address2' => $entity->address2,
                'city'     => $entity->city,
                'state'    => $entity->state, // TODO: always convert state to abbreviations?
                'zip'      => $entity->zip,
                'country'  => 'US',
            ]);
        } else {
            return null;
        }
    }

    /**
     * Convert from string or PhoneNumber to a properly formatted E164 phone number.
     *
     * @param PhoneNumber|string $phone
     * @return null|string
     */
    public function convertPhone($phone): ?string
    {
        try {
            if ($phone instanceof PhoneNumber) {
                $phone = $phone->number;
            }

            return PhoneNumber::formatE164($phone);
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);

            return null;
        }
    }

    /**
     * Convert datetime to expected ISO format and timezone.
     *
     * @param Carbon $date
     * @param string $timezone
     * @return string
     */
    public function convertDateTime(Carbon $date, string $timezone): string
    {
        return $date->tz($timezone)->toIso8601String();
    }
}
