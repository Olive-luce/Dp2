<?php
/**
 * Reusable map location picker. Sets hidden latitude/longitude fields and a
 * reverse-geocoded, still-editable address field.
 *
 * Optional variables before including:
 *   $pickerLatitude, $pickerLongitude, $pickerAddress  pre-selected values
 *   $pickerLabel                                       heading shown above the map
 */
$pickerLatitude = $pickerLatitude ?? '';
$pickerLongitude = $pickerLongitude ?? '';
$pickerAddress = $pickerAddress ?? '';
$pickerLabel = $pickerLabel ?? 'Location';
?>
<div class="mb-3" data-location-picker>
    <label class="form-label" for="pickerAddress"><?php echo htmlspecialchars($pickerLabel); ?></label>
    <div class="input-group mb-2">
        <input type="search" class="form-control" data-picker-search placeholder="Search for a place">
        <button class="btn btn-outline-primary" type="button" data-picker-search-button>Search</button>
        <button class="btn btn-outline-primary" type="button" data-picker-locate title="Use my current location"><i class="fa-solid fa-location-crosshairs"></i></button>
    </div>
    <div class="location-picker-map" data-picker-map></div>
    <p class="form-text" data-picker-status>Click the map to set the exact location, or search for a place.</p>
    <input class="form-control" id="pickerAddress" name="address" data-picker-address value="<?php echo htmlspecialchars($pickerAddress); ?>" placeholder="Address or landmark">
    <input type="hidden" name="latitude" data-picker-lat value="<?php echo htmlspecialchars($pickerLatitude); ?>">
    <input type="hidden" name="longitude" data-picker-lng value="<?php echo htmlspecialchars($pickerLongitude); ?>">
</div>
