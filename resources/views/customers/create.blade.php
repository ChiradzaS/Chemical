<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Customer</title>
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #1e293b;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .topbar {
            background: #0f172a;
            color: #fff;
            padding: 0 24px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar h1 {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }
        .topbar .badge {
            background: #0ea5e9;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 2px 9px;
            border-radius: 99px;
        }
        .topbar-actions {
            display: flex;
            gap: 10px;
        }
        .btn-cancel {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: color .15s, border-color .15s;
        }
        .btn-cancel:hover { color: #fff; border-color: #64748b; }
        .btn-submit {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 22px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-submit:hover { background: #0284c7; }

        /* ── Alerts ── */
        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 24px;
            font-size: .85rem;
            flex-shrink: 0;
        }
        /* Summary shown at the top when the server rejects the form */
        .alert-summary {
            background: #fee2e2;
            border-bottom: 1px solid #fca5a5;
            color: #991b1b;
            padding: 10px 24px;
            font-size: .82rem;
            flex-shrink: 0;
        }
        .alert-summary strong { font-weight: 800; }
        .alert-summary ul { margin: 4px 0 0 18px; }

        /* ── Scrollable body ── */
        .form-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px 32px;
        }

        /* ── Two-column page layout ── */
        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            align-items: start;
        }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .card-header {
            background: #0f172a;
            color: #fff;
            padding: 8px 16px;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .card-header .dot {
            width: 6px; height: 6px;
            background: #0ea5e9;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .card-body { padding: 14px 16px; }

        /* ── Fields ── */
        .grid { display: grid; gap: 10px; }
        .g2 { grid-template-columns: 1fr 1fr; }
        .g3 { grid-template-columns: 1fr 1fr 1fr; }
        .g4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
        .span2 { grid-column: span 2; }
        .span3 { grid-column: span 3; }
        .span4 { grid-column: span 4; }

        .field { display: flex; flex-direction: column; gap: 3px; }
        .field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .req { color: #ef4444; }

        input[type="text"],
        input[type="email"],
        textarea,
        select {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 5px 10px;
            font-size: .82rem;
            color: #1e293b;
            background: #fff;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }
        textarea { resize: vertical; min-height: 62px; }

        /* Field the server rejected */
        input.has-error, select.has-error {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: .75rem;
            margin-top: 3px;
        }

        .person-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .person-row:last-child { border-bottom: none; padding-bottom: 0; }
        .person-num {
            font-size: .65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding-top: 2px;
            grid-column: 1 / -1;
            margin-bottom: -4px;
        }

        /* Select2 tweaks */
        .select2-container--classic .select2-selection--single {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 7px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            font-size: .82rem !important;
        }
        .select2-container { width: 100% !important; }
    </style>
</head>
<body>

{{-- novalidate: the required fields sit in both columns, so a native browser
     bubble can point at a field that is scrolled out of view. The server
     validates everything and the errors render inline where they belong. --}}
<form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" novalidate style="display:contents">
@csrf

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({ theme: "classic" });

    // jump to the first rejected field so it is never missed
    var firstError = $('.has-error').first();
    if (firstError.length) {
        firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.trigger('focus');
    }
});
</script>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>New Customer</h1>
        <span class="badge">CRM</span>
    </div>
    <div class="topbar-actions">
        <a href="{{ url()->previous() }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="btn-submit">Save Customer</button>
    </div>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif



{{-- ── Scrollable content ── --}}
<div class="form-body">
<div class="page-grid">

    {{-- ══ LEFT COLUMN ══ --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Identity --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Identity</div>
            <div class="card-body">
                <div class="grid g3">
                    <div class="field span2">
                        <label>Customer Name <span class="req">*</span></label>
                        <input type="text" name="name" placeholder="e.g. Acme Corporation" required
                               class="@error('name') has-error @enderror" value="{{ old('name') }}">
                        @error('name')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Customer Type <span class="req">*</span></label>
                        <select name="customerType" class="js-example-basic-single @error('customerType') has-error @enderror" required>
                            <option value="" disabled {{ old('customerType') ? '' : 'selected' }} hidden>-- Select --</option>
                            @foreach($customertypes as $ct)
                                <option value="{{ $ct->id }}" {{ old('customerType') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                            @endforeach
                        </select>
                        @error('customerType')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Legal Name</label>
                        <input type="text" name="legalName" value="{{ old('legalName') }}">
                    </div>
                    <div class="field">
                        <label>Company Number</label>
                        <input type="text" name="companyNumber" value="{{ old('companyNumber') }}">
                    </div>
                    <div class="field">
                        <label>VAT Number <span class="req">*</span></label>
                        <input type="text" name="vatNo" required
                               class="@error('vatNo') has-error @enderror" value="{{ old('vatNo') }}">
                        @error('vatNo')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Account Number</label>
                        <input type="text" name="accountNumber" value="{{ old('accountNumber') }}">
                    </div>
                    <div class="field">
                        <label>Discount %</label>
                        <input type="text" name="discount" placeholder="0" value="{{ old('discount') }}">
                    </div>
                    <div class="field">
                        <label>Date Created</label>
                        <input type="text" name="dateCreated" value="{{ old('dateCreated') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Contact</div>
            <div class="card-body">
                <div class="grid g3">
                    <div class="field span3">
                        <label>Email</label>
                        <input type="email" name="emailAddress" placeholder="info@company.com" value="{{ old('emailAddress') }}">
                        @error('emailAddress')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phoneNumber" value="{{ old('phoneNumber') }}">
                    </div>
                    <div class="field">
                        <label>Mobile</label>
                        <input type="text" name="mobileNumber" value="{{ old('mobileNumber') }}">
                    </div>
                    <div class="field">
                        <label>Fax</label>
                        <input type="text" name="faxNumber" value="{{ old('faxNumber') }}">
                    </div>
                    <div class="field">
                        <label>Contact No</label>
                        <input type="text" name="contactNo" value="{{ old('contactNo') }}">
                    </div>
                    <div class="field">
                        <label>DDI Number</label>
                        <input type="text" name="dDINumber" value="{{ old('dDINumber') }}">
                    </div>
                    <div class="field">
                        <label>Skype</label>
                        <input type="text" name="skypeName" value="{{ old('skypeName') }}">
                    </div>
                    <div class="field span2">
                        <label>Website</label>
                        <input type="text" name="website" placeholder="https://…" value="{{ old('website') }}">
                    </div>
                    <div class="field">
                        <label>Contact Person (First)</label>
                        <input type="text" name="contactPerson" value="{{ old('contactPerson') }}">
                    </div>
                    <div class="field">
                        <label>Contact Person (Last)</label>
                        <input type="text" name="contactPersonLastName" value="{{ old('contactPersonLastName') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Banking --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Banking</div>
            <div class="card-body">
                <div class="grid g3">
                    <div class="field">
                        <label>Account Name</label>
                        <input type="text" name="bankAccountName" value="{{ old('bankAccountName') }}">
                    </div>
                    <div class="field">
                        <label>Account Number</label>
                        <input type="text" name="bankAccountNumber" value="{{ old('bankAccountNumber') }}">
                    </div>
                    <div class="field">
                        <label>Particulars</label>
                        <input type="text" name="bankAccountParticulars" value="{{ old('bankAccountParticulars') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Accounts & Tax --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Accounts &amp; Tax</div>
            <div class="card-body">
                <div class="grid g3">
                    <div class="field">
                        <label>Sales Account</label>
                        <input type="text" name="salesAccount" value="{{ old('salesAccount') }}">
                    </div>
                    <div class="field">
                        <label>Purchase Account</label>
                        <input type="text" name="purchaseAccount" value="{{ old('purchaseAccount') }}">
                    </div>
                    <div class="field">
                        <label>Branding Theme</label>
                        <input type="text" name="brandingTheme" value="{{ old('brandingTheme') }}">
                    </div>
                    <div class="field">
                        <label>Default Tax (Sales)</label>
                        <input type="text" name="defaultTaxSales" value="{{ old('defaultTaxSales') }}">
                    </div>
                    <div class="field">
                        <label>Default Tax (Bills)</label>
                        <input type="text" name="defaultTaxBills" value="{{ old('defaultTaxBills') }}">
                    </div>
                    <div class="field">
                        <label>AR Tax Code</label>
                        <input type="text" name="accountsRecevablesTaxCodeName" value="{{ old('accountsRecevablesTaxCodeName') }}">
                    </div>
                    <div class="field span3">
                        <label>AP Tax Code</label>
                        <input type="text" name="accountsPayableTaxCodeName" value="{{ old('accountsPayableTaxCodeName') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Due Dates --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Due Dates</div>
            <div class="card-body">
                <div class="grid g4">
                    <div class="field">
                        <label>Bill Day</label>
                        <input type="text" name="dueDateBillDay" value="{{ old('dueDateBillDay') }}">
                    </div>
                    <div class="field">
                        <label>Bill Term</label>
                        <input type="text" name="dueDateBillTerm" value="{{ old('dueDateBillTerm') }}">
                    </div>
                    <div class="field">
                        <label>Sales Day</label>
                        <input type="text" name="dueDateSalesDay" value="{{ old('dueDateSalesDay') }}">
                    </div>
                    <div class="field">
                        <label>Sales Term</label>
                        <input type="text" name="dueDateSalesTerm" value="{{ old('dueDateSalesTerm') }}">
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end left --}}

    {{-- ══ RIGHT COLUMN ══ --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Postal Address --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Postal Address</div>
            <div class="card-body">
                <div class="grid g2">
                    <div class="field span2">
                        <label>Attention To</label>
                        <input type="text" name="pOAttentionTo" value="{{ old('pOAttentionTo') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 1 <span class="req">*</span></label>
                        <input type="text" name="pOAddressLine1" required
                               class="@error('pOAddressLine1') has-error @enderror" value="{{ old('pOAddressLine1') }}">
                        @error('pOAddressLine1')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Address Line 2</label>
                        <input type="text" name="pOAddressLine2" value="{{ old('pOAddressLine2') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 3</label>
                        <input type="text" name="pOAddressLine3" value="{{ old('pOAddressLine3') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 4</label>
                        <input type="text" name="pOAddressLine4" value="{{ old('pOAddressLine4') }}">
                    </div>
                    <div class="field">
                        <label>City <span class="req">*</span></label>
                        <input type="text" name="pOCity" required
                               class="@error('pOCity') has-error @enderror" value="{{ old('pOCity') }}">
                        @error('pOCity')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Region <span class="req">*</span></label>
                        <input type="text" name="pORegion" required
                               class="@error('pORegion') has-error @enderror" value="{{ old('pORegion') }}">
                        @error('pORegion')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Postal Code <span class="req">*</span></label>
                        <input type="text" name="pOPostalCode" required
                               class="@error('pOPostalCode') has-error @enderror" value="{{ old('pOPostalCode') }}">
                        @error('pOPostalCode')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Country <span class="req">*</span></label>
                        <input type="text" name="pOCountry" required
                               class="@error('pOCountry') has-error @enderror" value="{{ old('pOCountry') }}">
                        @error('pOCountry')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Street Address --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Street Address</div>
            <div class="card-body">
                <div class="grid g2">
                    <div class="field span2">
                        <label>Attention To</label>
                        <input type="text" name="sAAttentionTo" value="{{ old('sAAttentionTo') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 1</label>
                        <input type="text" name="sAAttentionLine1" value="{{ old('sAAttentionLine1') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 2</label>
                        <input type="text" name="sAAttentionLine2" value="{{ old('sAAttentionLine2') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 3</label>
                        <input type="text" name="sAAttentionLine3" value="{{ old('sAAttentionLine3') }}">
                    </div>
                    <div class="field">
                        <label>Address Line 4</label>
                        <input type="text" name="sAAttentionLine4" value="{{ old('sAAttentionLine4') }}">
                    </div>
                    <div class="field">
                        <label>City</label>
                        <input type="text" name="sACity" value="{{ old('sACity') }}">
                    </div>
                    <div class="field">
                        <label>Region</label>
                        <input type="text" name="sARegion" value="{{ old('sARegion') }}">
                    </div>
                    <div class="field">
                        <label>Postal Code</label>
                        <input type="text" name="sAPostalCode" value="{{ old('sAPostalCode') }}">
                    </div>
                    <div class="field">
                        <label>Country</label>
                        <input type="text" name="sACountry" value="{{ old('sACountry') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Tracking --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Tracking</div>
            <div class="card-body">
                <div class="grid g3">
                    <div class="field">
                        <label>Tracking Name 1</label>
                        <input type="text" name="trackingName1" value="{{ old('trackingName1') }}">
                    </div>
                    <div class="field">
                        <label>Sales Option 1</label>
                        <input type="text" name="salesTrackingOption1" value="{{ old('salesTrackingOption1') }}">
                    </div>
                    <div class="field">
                        <label>Purchases Option 1</label>
                        <input type="text" name="purchasesTrackingOption1" value="{{ old('purchasesTrackingOption1') }}">
                    </div>
                    <div class="field">
                        <label>Tracking Name 2</label>
                        <input type="text" name="trackingName2" value="{{ old('trackingName2') }}">
                    </div>
                    <div class="field">
                        <label>Sales Option 2</label>
                        <input type="text" name="salesTrackingOption2" value="{{ old('salesTrackingOption2') }}">
                    </div>
                    <div class="field">
                        <label>Purchases Option 2</label>
                        <input type="text" name="purchasesTrackingOption2" value="{{ old('purchasesTrackingOption2') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Contacts --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Additional Contacts</div>
            <div class="card-body">
                @foreach([1,2,3,4,5] as $n)
                <div class="person-row">
                    <span class="person-num">Person {{ $n }}</span>
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="person{{ $n }}FirstName" value="{{ old('person'.$n.'FirstName') }}">
                    </div>
                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="person{{ $n }}SecondName" value="{{ old('person'.$n.'SecondName') }}">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="person{{ $n }}Email" value="{{ old('person'.$n.'Email') }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Notes --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Notes</div>
            <div class="card-body">
                <div class="field">
                    <label>Other Information</label>
                    <textarea name="otherinfo" placeholder="Any additional notes…">{{ old('otherinfo') }}</textarea>
                </div>
            </div>
        </div>

    </div>{{-- end right --}}

</div>{{-- end page-grid --}}
</div>{{-- end form-body --}}

</form>
</body>
</html>