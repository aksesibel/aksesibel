<label for="type_name">Nama *</label>
<input id="type_name" name="type_name" value="{{ old('type_name', $accessibilityType->type_name ?? '') }}" required>
@error('type_name') <p role="alert" class="galat">{{ $message }}</p> @enderror

<label for="description">Deskripsi</label>
<textarea id="description" name="description">{{ old('description', $accessibilityType->description ?? '') }}</textarea>