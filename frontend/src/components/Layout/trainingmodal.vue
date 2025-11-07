<script setup>
import { ref, watch, onBeforeUnmount } from "vue";
import QrcodeVue from "qrcode.vue";

const props = defineProps({
  isOpen: Boolean,
  training: Object,
  isRegistered: Boolean,
  isBookmarked: Boolean,
  bookmarkLoading: Boolean,
});

const emit = defineEmits(["close", "toggle-register", "bookmark"]);

// ⏳ QR countdown state
const countdown = ref("");
const qrExpired = ref(false);
let countdownInterval = null;

// 🕒 Countdown logic
watch(
  () => props.training,
  (t) => {
    if (!t || !t.end_time) {
      countdown.value = "";
      qrExpired.value = true;
      return;
    }

    const endTime = new Date(t.end_time);
    clearInterval(countdownInterval);

    const update = () => {
      const now = new Date();
      const diff = endTime - now;

      if (diff <= 0) {
        countdown.value = "00:00";
        qrExpired.value = true;
        clearInterval(countdownInterval);
      } else {
        const m = Math.floor(diff / 60000)
          .toString()
          .padStart(2, "0");
        const s = Math.floor((diff % 60000) / 1000)
          .toString()
          .padStart(2, "0");
        countdown.value = `${m}:${s}`;
        qrExpired.value = false;
      }
    };

    update();
    countdownInterval = setInterval(update, 1000);
  },
  { immediate: true }
);

onBeforeUnmount(() => clearInterval(countdownInterval));

// 📅 Helpers
function formatDate(datetime) {
  if (!datetime) return "N/A";
  return new Date(datetime).toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

function formatTime(datetime) {
  if (!datetime) return "N/A";
  return new Date(datetime).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
}
</script>

<!-- components/TrainingModal.vue -->
<template>
  <dialog v-if="isOpen" open class="modal sm:modal-middle">
    <div class="modal-box max-w-3xl relative font-poppins">
      <!-- Close button -->
      <button
        class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
        @click="$emit('close')"
      >
        ✕
      </button>

      <template v-if="training">
        <!-- Title & Organization -->
        <h2 class="text-xl font-bold">
          {{ training.title || "Untitled Training" }}
        </h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization:
          {{
            training.organization?.name ||
            training.organizationName ||
            "Unknown"
          }}
        </p>
        <!-- Buttons -->
        <!-- Buttons -->
      <div class="my-4 flex justify-end gap-2">
        <button
          class="btn btn-outline btn-sm flex items-center justify-center space-x-2"
          @click="$emit('bookmark', training.trainingID)"
          :disabled="bookmarkLoading"
        >
          <svg
            v-if="bookmarkLoading"
            class="animate-spin h-4 w-4 text-gray-600"
            xmlns="http://www.w3.org/2000/svg"
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
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
            ></path>
          </svg>

          <span v-else>{{ isBookmarked ? "Bookmarked" : "Bookmark" }}</span>
        </button>

        <button
          class="btn btn-sm text-white"
          :class="isRegistered ? 'bg-gray-500' : 'bg-customButton'"
          @click="$emit('toggle-register', training)"
        >
          {{ isRegistered ? "Unregister" : "Register" }}
        </button>
      </div>
        <p><strong>Mode:</strong> {{ training.mode || training.Mode }}</p>
        <!-- Description & Mode -->
        <p><strong>Description:</strong> {{ training.description }}</p>

        <!-- Location / Link -->
        <p v-if="training.mode?.toLowerCase() === 'online'">
          <strong>Link:</strong>
          <a
            :href="training.trainingLink"
            target="_blank"
            class="text-blue-500 underline"
          >
            {{ training.trainingLink }}
          </a>
        </p>
        <p v-else><strong>Location:</strong> {{ training.location }}</p>

        <!-- Schedule -->
        <p class="mb-4">
          <strong>Schedule:</strong> {{ formatDate(training.schedule) }}
          <span v-if="training.schedule"
            >at {{ formatTime(training.schedule) }}</span
          >
        </p>

        <!-- QR Code -->
        <div v-if="isRegistered" class="mt-4 text-center">
          <div
            v-if="training.attendance_key && !qrExpired"
            class="flex flex-col items-center"
          >
            <qrcode-vue :value="training.attendance_link" :size="120" />
            <p class="text-sm text-gray-600 mt-1">
              Expires in: {{ countdown }}
            </p>
          </div>
          <div v-else>
            <p class="text-sm text-gray-500">
              QR code not yet generated or expired.
            </p>
          </div>
        </div>
      </template>
    </div>
  </dialog>
</template>
