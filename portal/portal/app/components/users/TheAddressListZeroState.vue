<template>
    <!-- TODO: Style zero state correctly -->
    <div class="flex items-center justify-center h-64 text-gray-300">
        <div class="text-center">
            <font-awesome-icon :icon="['fad', 'address-card']" class="text-6xl" />            
            <p class="mt-4">
                {{ $t("No addresses found") }}
            </p>
        </div>
    </div>  
</template>

<script>
export default {
    name: "TheAddressListZeroState",
}
</script>