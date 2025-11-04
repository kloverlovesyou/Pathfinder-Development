<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue";

// ---------------------------
// Reactive States
// ---------------------------
const trainings = ref([]);
const organizations = ref([]);
const bookmarkedTrainings = ref([]);
const myRegistrations = ref(new Set());
const qrCountdowns = reactive({});
let qrIntervals = {};
const qrCodeValue = ref(null);
const selectedTraining = ref(null);
const isModalOpen = ref(false);
const toasts = ref([]);
const selectedPost = ref(null);

// ---------------------------
// Token Helper
// ---------------------------
function getToken() {
  return localStorage.getItem("token");
}

// ---------------------------
// Toast
// ---------------------------
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ---------------------------
// Fetch
// ---------------------------
async function fetchTrainings() {
  try {
    const token = getToken();
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings", {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    console.log("Trainings API response:", res.data);
    trainings.value = res.data;
  } catch {
    addToast("Failed to load trainings", "error");
  }
}

async function fetchBookmarks() {
  const token = getToken();
  if (!token) return;
  const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
    headers: { Authorization: `Bearer ${token}` },
  });
  bookmarkedTrainings.value = data;
}

async function fetchOrganizations() {
  try {
    const token = getToken();
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/organization", {
      headers: { Authorization: `Bearer ${token}` },
    });
    organizations.value = res.data;
  } catch {}
}

async function fetchMyRegistrations() {
  const token = getToken();
  if (!token) return;
  const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
    headers: { Authorization: `Bearer ${token}` },
  });

  myRegistrations.value.clear();
  res.data.forEach((r) => myRegistrations.value.add(r.trainingID));
}

// ---------------------------
// Bookmark
// ---------------------------
function isTrainingBookmarked(id) {
  return bookmarkedTrainings.value.includes(id);
}

// ---------------------------
// Training Modal
// ---------------------------
function openTrainingListModal(training) {
  selectedTraining.value = training;
}

function closeTrainingListModal() {
  selectedTraining.value = null;
}

// ---------------------------
// Computed
// ---------------------------
const trainingsWithOrg = computed(() =>
  trainings.value.map((t) => {
    const org = organizations.value.find((o) => o.organizationID === t.organizationID);
    return {
      ...t,
      organizationName: org ? org.name : "Unknown",
    };
  })
);

// ✅ FIXED — only *one* function
function startAllQRCountdowns() {
  Object.values(qrIntervals).forEach(clearInterval);
  qrIntervals = {};

  trainingsWithOrg.value.forEach((training) => {
    if (!myRegistrations.value.has(training.trainingID) || !training.attendance_expires_at) return;

    const id = training.trainingID;
    const expires = new Date(training.attendance_expires_at);

    const update = () => {
      const diff = expires - new Date();
      if (diff <= 0) {
        qrCountdowns[id] = "00:00";
        clearInterval(qrIntervals[id]);
      } else {
        const m = Math.floor(diff / 60000).toString().padStart(2, "0");
        const s = Math.floor((diff % 60000) / 1000).toString().padStart(2, "0");
        qrCountdowns[id] = `${m}:${s}`;
      }
    };

    qrCountdowns[id] = "loading...";
    update();
    qrIntervals[id] = setInterval(update, 1000);
  });
}

// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  await fetchOrganizations();
  await fetchTrainings();
  await fetchBookmarks();
  await fetchMyRegistrations();
});

// ✅ FIXED WATCH – will now work in production
watch([trainings, myRegistrations], async () => {
  console.log("✅ Trainings changed:", trainings.value);
  await nextTick();
  startAllQRCountdowns();
});
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
        <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">
          Training
        </h2>
      </div>
      <!-- Training Cards -->
      <div class="space-y-4">
        <div
          v-for="training in trainingsWithOrg"
          :key="training.trainingID"
          class="p-4 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer flex justify-between items-center"
          @click="openTrainingListModal(training)"
        >
          <!-- Left: Training info -->
          <div>
            <h3 class="font-semibold text-lg">{{ training.title }}</h3>
            <p class="text-gray-700 font-medium">
              {{ training.organization?.name || training.organizationName }}
            </p>
          </div>

          <!-- Right: QR code if registered -->
          <div v-if="myRegistrations.has(training.trainingID)">
            <div
              v-if="
                training.attendance_key &&
                new Date(training.end_time) > new Date()
              "
            >
              <qrcode-vue :value="training.attendance_key" :size="80" />
            </div>
            <div v-else>
              <p class="text-sm text-gray-500 text-center">
                QR code not yet generated or expired.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <!--Training List Modal-->
    <dialog v-if="selectedTrainingForList" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingListModal"
        >
          ✕
        </button>

        <!-- Safety check wrapper -->
        <template v-if="selectedTrainingForList">
          <!-- Training Details -->
          <h2 class="text-xl font-bold mb-2">
            {{ selectedTrainingForList.title || "Untitled Training" }}
          </h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization:
            {{
              selectedTrainingForList.organization?.name ||
              selectedTrainingForList.organizationName ||
              "Unknown Organization"
            }}
          </p>

          <!-- Buttons -->
          <div
            class="my-4 flex justify-end gap-2"
            v-if="selectedTrainingForList.trainingID"
          >
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedTrainingForList.trainingID)"
            >
              {{
                bookmarkedPosts?.[selectedTrainingForList.trainingID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Register / Unregister -->
            <button
              class="btn btn-sm text-white"
              :class="
                registeredPosts[selectedTrainingForList.trainingID]
                  ? 'bg-gray-500'
                  : 'bg-customButton'
              "
              @click.stop="toggleRegister(selectedTrainingForList)"
            >
              {{
                registeredPosts[selectedTrainingForList.trainingID]
                  ? "Unregister"
                  : "Register"
              }}
            </button>
          </div>

          <!-- Basic Info -->
          <p><strong>Mode:</strong> {{ selectedTrainingForList.mode }}</p>
          <p>
            <strong>Description:</strong>
            {{ selectedTrainingForList.description }}
          </p>

          <!-- Conditional Display -->
          <p v-if="selectedTrainingForList.mode?.toLowerCase() === 'online'">
            <strong>Link:</strong>
            <a
              :href="selectedTrainingForList.trainingLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedTrainingForList.trainingLink }}
            </a>
          </p>

          <p
            v-else-if="
              selectedTrainingForList.mode?.toLowerCase() === 'on-site'
            "
          >
            <strong>Location:</strong> {{ selectedTrainingForList.location }}
          </p>

          <p>
            <strong>Schedule:</strong>
            {{ formatDate(selectedTrainingForList.schedule) }}
            <span v-if="selectedTrainingForList.schedule">
              at {{ formatTime(selectedTrainingForList.schedule) }}
            </span>
          </p>

          <!-- QR code if registered -->
          <div
            v-if="myRegistrations.has(selectedTrainingForList.trainingID)"
            class="mt-4 text-center"
          >
            <div
              v-if="
                selectedTrainingForList.attendance_key &&
                new Date(training.end_time) > new Date()
              "
              class="text-center flex flex-col items-center justify-center"
            >
              <qrcode-vue
                :value="selectedTrainingForList.attendance_key"
                :size="120"
              />
              <p class="text-sm text-gray-600 mt-1">
                Expires in:
                {{
                  qrCountdowns[selectedTrainingForList.trainingID] ||
                  formatDateTime(selectedTrainingForList.end_time)
                }}
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

    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="alert"
        :class="{
          'alert-info': toast.type === 'info',
          'alert-success': toast.type === 'success',
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </main>
</template>
