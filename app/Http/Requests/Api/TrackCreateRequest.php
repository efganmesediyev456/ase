<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'string'],
            'delivery_number' => ['required', 'string'],
            'type' => ['required', 'string', 'in:ST'],
            'seller' => ['required', 'array'],
//            'seller.full_name' => ['required', 'string'],
//            'seller.email_address' => ['required', 'email'],
//            'seller.phone_number' => ['required', 'string'],
//            'seller.zip_code' => ['required', 'string'],
//            'seller.city' => ['required', 'string'],
//            'seller.country' => ['required', 'string'],
//            'seller.address' => ['nullable', 'string'],
            'buyer' => ['required', 'array'],
            'buyer.first_name' => ['required', 'string'],
            'buyer.last_name' => ['required', 'string'],
//            'buyer.email_address' => ['required', 'email'],
            'buyer.phone_number' => ['required', 'string'],
            'buyer.pin_code' => ['required', 'string'],
            'buyer.city' => ['required', 'string'],
            'buyer.country' => ['required', 'string'],
            'buyer.zip_code' => ['required', 'string'],
            'buyer.longitude' => ['nullable', 'string'],
            'buyer.latitude' => ['nullable', 'string'],
            'buyer.shipping_address' => ['required', 'string'],
            'buyer.billing_address' => ['required', 'string'],
            'invoice' => ['required', 'array'],
            'invoice.invoice_price' => ['required', 'numeric'],
//            'invoice.invoice_due_date' => ['required', 'date'],
//            'invoice.invoice_base64' => ['required', 'string'],
//            'invoice.invoice_url' => ['required', 'url'],
            'invoice.currency' => ['required', 'string', 'in:USD,KZT'],
            'shipping_invoice' => ['required', 'array'],
            'shipping_invoice.invoice_price' => ['required', 'numeric'],
//            'shipping_invoice.invoice_due_date' => ['required', 'date'],
//            'shipping_invoice.invoice_base64' => ['required', 'string'],
//            'shipping_invoice.invoice_url' => ['required', 'url'],
            'shipping_invoice.currency' => ['required', 'string', 'in:USD,KZT'],
            'comment' => ['nullable', 'string'],
            'is_liquid' => ['required', 'boolean'],
//            'is_door' => ['sometimes', 'boolean'],
            'products' => ['required', 'array'],
            'from_country' => ['nullable', 'string'],
//            'products.*.sku' => ['required', 'string'],
            //'products.*.hs_code' => ['required', 'string'],
            'products.*.name' => ['required', 'string'],
//            'products.*.category' => ['required', 'string'],
            'products.*.unit_price' => ['required', 'numeric'],
            'products.*.quantity' => ['required', 'integer']
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Please include the Warehouse ID.',
            'delivery_number.required' => 'Please enter the Delivery Number.',
            'type.required' => 'Please specify the type.',
            'type.in' => 'Invalid type specified.',
            'seller.required' => 'Seller information is required.',
            'seller.full_name.required' => 'Seller full name is required.',
            'seller.email_address.required' => 'Seller email address is required.',
            'seller.email_address.email' => 'Seller email address must be a valid email.',
            'seller.phone_number.required' => 'Seller phone number is required.',
            'seller.zip_code.required' => 'Seller zip code is required.',
            'seller.city.required' => 'Seller city is required.',
            'seller.country.required' => 'Seller country is required.',
            'buyer.required' => 'Please provide the Buyer\'s information.',
            'buyer.first_name.required' => 'Buyer\'s first name is required.',
            'buyer.last_name.required' => 'Buyer\'s last name is required.',
            'buyer.email_address.required' => 'Buyer\'s email address is required.',
            'buyer.email_address.email' => 'Buyer\'s email address must be a valid email.',
            'buyer.phone_number.required' => 'Buyer\'s phone number is required.',
            'buyer.pin_code.required' => 'Buyer\'s pin code is required.',
            'buyer.city.required' => 'Buyer\'s city is required.',
            'buyer.country.required' => 'Buyer\'s country is required.',
            'buyer.zip_code.required' => 'Buyer\'s zip code is required.',
            'buyer.shipping_address.required' => 'Buyer\'s shipping address is required.',
            'buyer.billing_address.required' => 'Buyer\'s billing address is required.',
            'invoice.required' => 'Invoice information is required.',
            'invoice.invoice_price.required' => 'Invoice price is required.',
            'invoice.invoice_due_date.required' => 'Invoice due date is required.',
            'invoice.invoice_base64.required' => 'Invoice base64 is required.',
            'invoice.invoice_url.required' => 'Invoice URL is required.',
            'invoice.currency.required' => 'Invoice currency is required.',
            'invoice.currency.in' => 'Invoice currency must be USD.',
            'shipping_invoice.required' => 'Shipping invoice information is required.',
            'shipping_invoice.invoice_price.required' => 'Shipping invoice price is required.',
            'shipping_invoice.invoice_due_date.required' => 'Shipping invoice due date is required.',
            'shipping_invoice.invoice_base64.required' => 'Shipping invoice base64 is required.',
            'shipping_invoice.invoice_url.required' => 'Shipping invoice URL is required.',
            'shipping_invoice.currency.required' => 'Shipping invoice currency is required.',
            'shipping_invoice.currency.in' => 'Shipping invoice currency must be USD.',
            'products.required' => 'Please include at least one product in the package.',
            'products.*.sku.required' => 'Product SKU is required.',
            'products.*.hs_code.required' => 'Product HS code is required.',
            'products.*.name.required' => 'Product name is required.',
            'products.*.category.required' => 'Product category is required.',
            'products.*.unit_price.required' => 'Product unit price is required.',
            'products.*.quantity.required' => 'Product quantity is required.',
            'is_liquid.required' => 'Please specify if the package contains liquid.',
            'is_liquid.boolean' => 'Is liquid must be a boolean value.',
            'is_door.required' => 'Please specify if the package is for door delivery.',
            'is_door.boolean' => 'Is door must be a boolean value.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = [
            'status' => false,
            'message' => $validator->errors()->first(),
            'data' => $validator->errors()
        ];
        throw new HttpResponseException(response()->json($response, 400));
    }
}
