@if (!empty($logo))
    <div class="logo-container">
        <img src="{{ $logo }}" class="logo"  alt=""/>
    </div>
@endif

<div class="header">
    <div>{{ $company_name }}</div>
    <div>{{ $company_representative }}</div>
    <div>{{ $company_address }}</div>
    <div>{{ $company_zipcode }} {{ $company_city }}</div>
</div>
