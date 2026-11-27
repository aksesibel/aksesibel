@php
    $isEdit     = isset($category);
    $title      = $isEdit ? 'Ubah Kategori' : 'Tambah Kategori';
    $action     = $isEdit ? route('admin.categories.update', $category->category_id) : route('admin.categories.store');
    $method     = $isEdit ? 'PUT' : 'POST';
    $nextOrder  = $nextOrder ?? ($isEdit ? $category->sort_order : 0);
@endphp

<x-layout :title="$title">

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    {{-- Breadcrumb --}}
    <nav aria-label="Lokasi halaman" class="mb-6">
        <ol class="flex items-center gap-2 text-sm text-[#475569]">
            <li><a href="{{ route('admin.categories.index') }}" class="hover:text-[#1D4ED8] transition-colors">Kelola Kategori</a></li>
            <li aria-hidden="true"><span class="mx-1 text-[#CBD5E1]">/</span></li>
            <li aria-current="page" class="text-[#0F172A] font-medium">{{ $title }}</li>
        </ol>
    </nav>

    <h1 class="text-2xl font-extrabold text-[#0F172A] mb-8">{{ $title }}</h1>

    <form action="{{ $action }}" method="POST"
          x-data="{
            name: '{{ old('category_name', $category->category_name ?? '') }}',
            slug: '{{ old('slug', $category->slug ?? '') }}',
            slugEdited: {{ $isEdit ? 'true' : 'false' }},
            generateSlug(val) {
                if (this.slugEdited) return;
                this.slug = val.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
          }"
          novalidate aria-label="{{ $title }}">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif

        @if($errors->any())
            <div role="alert" aria-live="assertive" class="mb-6 bg-[#FEE2E2] border border-red-200 text-[#B91C1C] rounded-xl p-4 text-sm">
                <p class="font-semibold mb-1">Mohon perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 space-y-6">

            {{-- Nama --}}
            <div>
                <label for="category_name" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                    Nama Kategori <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                </label>
                <input id="category_name" type="text" name="category_name"
                       x-model="name"
                       @input="generateSlug($event.target.value)"
                       required minlength="3" maxlength="60"
                       aria-required="true"
                       class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('category_name') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]"
                       placeholder="Contoh: Transportasi">
                @error('category_name')
                    <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                    Slug <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#94A3B8] shrink-0">/kategori/</span>
                    <input id="slug" type="text" name="slug"
                           x-model="slug"
                           @input="slugEdited = true"
                           required
                           aria-required="true"
                           aria-describedby="slug-hint"
                           class="flex-1 px-3 py-2.5 rounded-lg border {{ $errors->has('slug') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] font-mono focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                    <button type="button"
                            @click="slugEdited = false; generateSlug(name)"
                            class="shrink-0 px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs text-[#475569] hover:bg-[#F8FAFC] transition-colors min-h-[44px]">
                        Reset
                    </button>
                </div>
                <p id="slug-hint" class="mt-1 text-xs text-[#94A3B8]">Hanya huruf kecil, angka, dan tanda hubung. Terisi otomatis dari nama.</p>
                @error('slug')
                    <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-[#0F172A] mb-1.5">
                    Deskripsi <span class="text-[#94A3B8] font-normal">(opsional)</span>
                </label>
                <textarea id="description" name="description"
                          maxlength="300" rows="3"
                          aria-describedby="desc-cat-hint"
                          class="w-full px-3 py-2.5 rounded-lg border {{ $errors->has('description') ? 'border-[#B91C1C] bg-[#FEF2F2]' : 'border-[#E2E8F0]' }} text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] resize-y"
                          placeholder="Deskripsi singkat kategori ini…">{{ old('description', $category->description ?? '') }}</textarea>
                <p id="desc-cat-hint" class="mt-1 text-xs text-[#94A3B8]">Maksimal 300 karakter.</p>
                @error('description')
                    <p role="alert" aria-live="polite" class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pemilih ikon --}}
            <fieldset>
                <legend class="block text-sm font-semibold text-[#0F172A] mb-3">
                    Ikon <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                </legend>
                @error('icon')
                    <p role="alert" aria-live="polite" class="mb-2 text-xs text-[#B91C1C]">{{ $message }}</p>
                @enderror
                <div class="grid grid-cols-5 sm:grid-cols-10 gap-2" role="radiogroup" aria-label="Pilih ikon kategori">
                    @foreach($iconOptions as $iconKey => $iconLabel)
                        @php $checked = old('icon', $category->icon ?? 'Building2') === $iconKey; @endphp
                        <label class="flex flex-col items-center gap-1 cursor-pointer group" title="{{ $iconLabel }}">
                            <input type="radio" name="icon" value="{{ $iconKey }}"
                                   {{ $checked ? 'checked' : '' }}
                                   class="sr-only peer"
                                   aria-label="{{ $iconLabel }}">
                            <span class="w-10 h-10 rounded-lg border-2 flex items-center justify-center transition-colors
                                         border-[#E2E8F0] bg-white text-[#94A3B8]
                                         peer-checked:border-[#1D4ED8] peer-checked:bg-[#EFF6FF] peer-checked:text-[#1D4ED8]
                                         group-hover:border-[#1D4ED8] group-hover:text-[#1D4ED8]
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-[#1D4ED8]">
                                <x-category-icon :icon="$iconKey" class="w-5 h-5" />
                            </span>
                            <span class="text-[0.6rem] text-[#94A3B8] text-center leading-tight">{{ $iconLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Status & Urutan --}}
            <div class="flex flex-col sm:flex-row gap-6">
                <div>
                    <fieldset>
                        <legend class="block text-sm font-semibold text-[#0F172A] mb-2">Status</legend>
                        <label class="flex items-center gap-3 cursor-pointer min-h-[44px]"
                               x-data="{ checked: {{ old('is_active', $category->is_active ?? true) ? 'true' : 'false' }} }">
                            <div class="relative">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       x-model="checked"
                                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                                       class="sr-only peer"
                                       role="switch"
                                       :aria-checked="checked"
                                       aria-label="Kategori aktif">
                                <div class="w-10 h-6 rounded-full transition-colors peer-checked:bg-[#1D4ED8] bg-[#CBD5E1]
                                            peer-focus-visible:ring-2 peer-focus-visible:ring-[#1D4ED8] peer-focus-visible:ring-offset-2"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm text-[#475569]" x-text="checked ? 'Aktif' : 'Nonaktif'"></span>
                        </label>
                    </fieldset>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-[#0F172A] mb-1.5">Urutan Tampil</label>
                    <input id="sort_order" type="number" name="sort_order" min="0"
                           value="{{ old('sort_order', $category->sort_order ?? $nextOrder) }}"
                           class="w-24 px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] min-h-[44px]">
                </div>
            </div>

        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8">
            <a href="{{ route('admin.categories.index') }}"
               class="px-5 py-2.5 rounded-lg border border-[#E2E8F0] text-[#475569] text-sm font-medium hover:bg-[#F8FAFC] transition-colors min-h-[44px] flex items-center justify-center">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-semibold transition-colors min-h-[44px] flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Kategori' }}
            </button>
        </div>

    </form>
</div>

</x-layout>
