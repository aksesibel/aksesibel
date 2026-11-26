@if(session('success'))
    <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-900">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div role="alert" class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-900">
        <h2 class="font-semibold">Periksa kembali data berikut:</h2>
        <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
