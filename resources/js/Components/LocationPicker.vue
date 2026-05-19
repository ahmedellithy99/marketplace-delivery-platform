<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    height: { type: String, default: "300px" },
});

const emit = defineEmits(["update:latitude", "update:longitude"]);

const mapContainer = ref(null);
const locating = ref(false);
const locationError = ref("");

let map = null;
let marker = null;

// Default center: Cairo, Egypt
const defaultLat = 30.0444;
const defaultLng = 31.2357;
const defaultZoom = 13;

// Fix Leaflet default marker icon path issue with bundlers
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
    iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
    shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
});

function getInitialCenter() {
    const lat = parseFloat(props.latitude);
    const lng = parseFloat(props.longitude);
    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
        return [lat, lng];
    }
    return [defaultLat, defaultLng];
}

function updateCoordinates(lat, lng) {
    const latFixed = parseFloat(lat).toFixed(7);
    const lngFixed = parseFloat(lng).toFixed(7);
    emit("update:latitude", latFixed);
    emit("update:longitude", lngFixed);
}

function placeMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on("dragend", () => {
            const pos = marker.getLatLng();
            updateCoordinates(pos.lat, pos.lng);
        });
    }
}

function initMap() {
    if (!mapContainer.value) return;

    const center = getInitialCenter();

    map = L.map(mapContainer.value).setView(center, defaultZoom);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Place marker if we have initial coordinates
    const lat = parseFloat(props.latitude);
    const lng = parseFloat(props.longitude);
    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
        placeMarker(lat, lng);
    }

    // Click on map to place/move marker
    map.on("click", (e) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });
}

function useMyLocation() {
    if (!navigator.geolocation) {
        locationError.value = "المتصفح لا يدعم تحديد الموقع";
        return;
    }

    locating.value = true;
    locationError.value = "";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            placeMarker(lat, lng);
            updateCoordinates(lat, lng);
            map.setView([lat, lng], 16);
            locating.value = false;
        },
        (error) => {
            locating.value = false;
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = "تم رفض إذن تحديد الموقع";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = "الموقع غير متاح حالياً";
                    break;
                case error.TIMEOUT:
                    locationError.value = "انتهت مهلة تحديد الموقع";
                    break;
                default:
                    locationError.value = "حدث خطأ في تحديد الموقع";
            }
        },
        { enableHighAccuracy: true, timeout: 10000 },
    );
}

// Watch for external changes to lat/lng props
watch(
    () => [props.latitude, props.longitude],
    ([newLat, newLng]) => {
        const lat = parseFloat(newLat);
        const lng = parseFloat(newLng);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0 && map) {
            placeMarker(lat, lng);
            map.setView([lat, lng], map.getZoom());
        }
    },
);

onMounted(() => {
    nextTick(() => initMap());
});
</script>

<template>
    <div class="space-y-3">
        <!-- Map Container -->
        <div
            ref="mapContainer"
            class="w-full rounded-xl border border-gray-200 overflow-hidden z-0"
            :style="{ height }"
        ></div>

        <!-- Controls -->
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="useMyLocation"
                :disabled="locating"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg
                    v-if="!locating"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                <svg
                    v-else
                    class="w-4 h-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                {{ locating ? "جاري التحديد..." : "موقعي الحالي" }}
            </button>
            <p class="text-xs text-gray-400">
                أو اضغط على الخريطة لتحديد الموقع
            </p>
        </div>

        <!-- Error -->
        <p
            v-if="locationError"
            class="text-sm text-red-600 flex items-center gap-1"
        >
            <svg
                class="w-4 h-4 shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                />
            </svg>
            {{ locationError }}
        </p>
    </div>
</template>
