<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue"; // make sure to import this if using QR

const registeredPosts = reactive({}); // stores registered trainings

async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Fill registeredPosts (for quick lookup)
    res.data.forEach((r) => {
      registeredPosts[r.trainingID] = {
        registrationID: r.registrationID,
      };
    });

    console.log("✅ Registered trainings loaded:", registeredPosts);
  } catch (err) {
    console.error("❌ Failed to fetch registrations:", err);
  }
}

onMounted(fetchMyRegistrations);

function toggleBookmark(post) {
  const id = post.trainingID || post.careerID;
  bookmarkedPosts.value[id] = !bookmarkedPosts.value[id];
}
const bookmarkedPosts = ref({});

  /// Toggle registration
  async function toggleRegister(training) {
    const token = localStorage.getItem("token");
    if (!token) return alert("Please log in first.");

    // If already registered -> unregister
    if (registeredPosts[training.trainingID]) {
      try {
        const registrationID = registeredPosts[training.trainingID].registrationID;

        // ✅ Log the ID you're about to delete
        console.log("Deleting registration ID:", registrationID);

        await axios.delete(
          import.meta.env.VITE_API_BASE_URL + `/registrations/${registrationID}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        delete registeredPosts[training.trainingID];
        console.log(`🗑 Unregistered from ${training.title}`);
      } catch (err) {
        console.error("❌ Failed to unregister:", err);
      }
    }
    // Else register
    else {
      try {
        const res = await axios.post(
          import.meta.env.VITE_API_BASE_URL + "/registrations",
          { trainingID: training.trainingID },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        // ✅ FIXED HERE
        const regID = res.data.data.registrationID;
        registeredPosts[training.trainingID] = { registrationID: regID };

        console.log(`✅ Registered for ${training.title} with ID`, regID);
      } catch (err) {
        console.error("❌ Failed to register:", err);
      }
    }
  }

const props = defineProps({
  isOpen: { type: Boolean, required: true },
});
const emit = defineEmits(["open", "close", "eventClick"]);

const trainings = ref([]);
const myRegistrations = ref(new Set());

const selectedTraining = ref(null);

const selectedPost = ref(null);
function openModal(post) {
  console.log("Opening modal for:", post); // ✅ debug line

  if (post.type === "training") {
    selectedTraining.value = post; // 🟦 training modal
    selectedPost.value = null;
  } else if (post.type === "career") {
    selectedPost.value = post; // 🟩 career modal
    selectedTraining.value = null;
  } else {
    console.warn("Unknown post type:", post);
  }
}

function closeModal() {
  selectedPost.value = null;
  selectedTraining.value = null;
}

function formatDateTime(dt) {
  if (!dt) return "";
  return new Date(dt).toLocaleString("en-US", {
    dateStyle: "long",
    timeStyle: "short",
  });
}
function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", {
    dateStyle: "long",
  });
}

const uploadedFile = ref(null);
const applyModalOpen = ref(false);
function openApplyModal(post) {
  selectedPost.value = post;
  applyModalOpen.value = true;
}

function closeApplyModal() {
  applyModalOpen.value = false;
  uploadedFile.value = null;
}

function handleFileUpload(e) {
  uploadedFile.value = e.target.files[0];
}

function submitApplication() {
  if (!uploadedFile.value) {
    alert("Please upload a PDF file first.");
    return;
  }

  // ✅ Safety check: make sure a post is selected
  if (!selectedPost.value) return;

  // Log info (for debugging)
  console.log("Submitting application for:", selectedPost.value);
  console.log("Uploaded file:", uploadedFile.value);

  const id = selectedPost.value.careerID;
  appliedPosts.value[id] = true;

  alert("Application submitted successfully!");
  closeApplyModal();
}
// --- CALENDAR LOGIC ---
const calendarRef = ref(null);
const selectedDate = ref("");
const events = ref({}); // Map of date → [events]
const dayEvents = ref([]);

// --- Handle day click ---
function onDayClick(date) {
  selectedDate.value = toISODate(date);
  showEvents(selectedDate.value);
}

// --- Initialize calendar: fetch events & select today ---
onMounted(async () => {
  await fetchEvents();
  onDayClick(new Date()); // select today automatically
});
// --- Check if a date is today ---
function isToday(date) {
  const d = new Date(date);
  const now = new Date();
  return (
    d.getFullYear() === now.getFullYear() &&
    d.getMonth() === now.getMonth() &&
    d.getDate() === now.getDate()
  );
}
// --- EVENTS LOGIC ---

async function fetchEvents() {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    const token = localStorage.getItem("token");

    if (!user || !token) {
      console.warn("⚠️ No user or token found in localStorage");
      return;
    }

    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/calendar/${user.applicantID}`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const eventList = response.data.events || [];
    console.log("✅ API Events Fetched:", eventList);

    // Normalize all dates (ensure YYYY-MM-DD)
    eventList.forEach((e) => {
      e.date = new Date(e.date).toISOString().split("T")[0];
    });

    // Build events map
    events.value = {};
    eventList.forEach((event) => {
      if (!events.value[event.date]) events.value[event.date] = [];
      events.value[event.date].push(event);
    });

    console.log("📅 Events Map:", events.value);
  } catch (error) {
    console.error("❌ Error fetching events:", error);
  }
}

function showEvents(date) {
  selectedDate.value = date;
  dayEvents.value = events.value[date] || [];
  console.log("📅 Events for", date, ":", dayEvents.value);
}

// --- INITIALIZE CALENDAR ---
onMounted(async () => {
  await nextTick();
  await fetchEvents();

  const calendar = calendarRef.value;
  if (!calendar) return;

  const today = new Date().toISOString().split("T")[0];
  showEvents(today);

  // Highlight event days and today on render
  const highlightDays = () => {
    calendar.querySelectorAll("[data-date]").forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("event-day", "today");

      if (events.value[dateStr]) {
        el.classList.add("event-day");
      }

      if (dateStr === today) {
        el.classList.add("today"); // <-- Highlight today
      }
    });
  };

  const highlightToday = () => {
    // Select all day elements
    const dayEls = calendar.querySelectorAll("[data-date]");
    if (!dayEls.length) return;

    dayEls.forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("today");

      if (dateStr === today) {
        el.classList.add("today"); // Add highlight to today
      }
    });
  };

  // Initial highlight
  highlightDays();

  // Optional: re-highlight on calendar render if your calendar library triggers it
  calendar.addEventListener("render", highlightDays);

  // Handle day clicks
  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);
  });
});

// Format for comparisons / mapping
function toISODate(d) {
  if (!d) return "";
  return new Date(d).toISOString().split("T")[0]; // YYYY-MM-DD
}

const selectedEvent = ref(null);

// Check if QR should be visible
function isTrainingActive(training) {
  if (!training.schedule || !training.end_time) return false;

  const now = new Date();
  const start = new Date(training.schedule);
  const end = new Date(training.end_time);

  return now >= start && now <= end;
}

const registeredTrainingsWithQR = computed(() => {
  return trainingsWithOrg.value
    .filter((t) => myRegistrations.value.has(t.trainingID))
    .map((t) => {
      const isActiveQR = qrActiveTrainingId.value === t.trainingID;
      return {
        ...t,
        qrKey: t.attendance_key,
        qrExpires: t.end_Time,
        isActiveQR,
        qrCountdown: isActiveQR ? qrCountdown.value : null,
      };
    });
});

//QR Countdown
function startQRCountdown(training) {
  // Stop any running countdown interval
  if (qrCountdownInterval) clearInterval(qrCountdownInterval);

  // 📌 Use the training.end_time as expiration reference
  const endTime = new Date(training.end_time || training.end_Time); // supports both snakeCase or camelCase

  // Generate QR (you can change this to your full URL if needed)
  qrCodeValue.value = import.meta.env.VITE_API_BASE_URL + `/attendance/submit?trainingID=${training.trainingID}&key=${training.attendance_key}`;
  qrExpiresAt.value = endTime;
  qrActiveTrainingId.value = training.trainingID;

  // ⏳ Countdown function
  const updateCountdown = () => {
    const now = new Date();
    const diff = endTime - now;

    if (diff <= 0) {
      // ⛔ QR expired
      clearInterval(qrCountdownInterval);
      qrCountdown.value = "00:00";
      qrCodeValue.value = "expired";
      qrActiveTrainingId.value = null;
      qrExpiresAt.value = null;
    } else {
      const minutes = Math.floor(diff / 60000)
        .toString()
        .padStart(2, "0");
      const seconds = Math.floor((diff % 60000) / 1000)
        .toString()
        .padStart(2, "0");
      qrCountdown.value = `${minutes}:${seconds}`;
    }
  };

  // Start immediately and every second
  updateCountdown();
  qrCountdownInterval = setInterval(updateCountdown, 1000);
}

// Reactive object to hold countdowns for all trainings

// Start countdowns for all registered trainings with QR
function startAllQRCountdowns() {
  Object.values(qrIntervals).forEach(clearInterval);
  qrIntervals = {};

  //start all countdowns for existing QR codes
  function startAllQRCountdowns() {
    // Clear any previous intervals (This assumes qrIntervals is defined, see fix 1)
    Object.values(qrIntervals).forEach(clearInterval);
    qrIntervals = {};

    trainingsWithOrg.value.forEach((training) => {
      if (
        myRegistrations.value.has(training.trainingID) &&
        training.attendance_expires_at
      ) {
        const trainingId = training.trainingID;

        const updateCountdown = () => {
          const now = new Date();
          const expires = new Date(training.end_time);
          const diff = expires - now;

          if (diff <= 0) {
            qrCountdowns[trainingId] = "00:00";
            clearInterval(qrIntervals[trainingId]);
          } else {
            const minutes = Math.floor(diff / 60000)
              .toString()
              .padStart(2, "0");
            const seconds = Math.floor((diff % 60000) / 1000)
              .toString()
              .padStart(2, "0");
            qrCountdowns[trainingId] = `${minutes}:${seconds}`;
          }
        }; // Initialize the key so template reacts immediately

        qrCountdowns[trainingId] = "loading...";

        updateCountdown(); // initial call
        qrIntervals[trainingId] = setInterval(updateCountdown, 1000);
      }
    });
  }
  const modalQR = reactive({
    value: null,
    expiresAt: null,
    countdown: "00:00",
    interval: null,
  });

  //ModalQrCountdown
  // Modal QR Countdown
  function startModalQRCountdown(training) {
    if (!training.attendance_key || !training.end_time) return;

    qrCodeValue.value = training.attendance_key;
    qrCountdown.value = "";

    const expiresAt = new Date(training.end_time);

    if (qrInterval) clearInterval(qrInterval);

    const updateCountdown = () => {
      const now = new Date();
      const diff = expiresAt - now;

      if (diff <= 0) {
        // ⛔ Hide QR and stop countdown
        qrCountdown.value = "00:00";
        qrCodeValue.value = null; // hide QR code
        clearInterval(qrInterval);

        // Optional: if you’re showing a modal or section, hide it too
        if (isModalOpen.value) isModalOpen.value = false;
      } else {
        const minutes = Math.floor(diff / 60000)
          .toString()
          .padStart(2, "0");
        const seconds = Math.floor((diff % 60000) / 1000)
          .toString()
          .padStart(2, "0");
        qrCountdown.value = `${minutes}:${seconds}`;
      }
    };

    updateCountdown();
    qrInterval = setInterval(updateCountdown, 1000);
  }
}

// ============================
// 🚀 Lifecycle Hooks
// ============================
onMounted(async () => {
  await fetchOrganizations();
  console.log("✅ Organizations loaded:", organizations.value); // <-- here

  const orgID = 1; // replace with dynamic value
  await fetchTrainings(orgID);
  console.log("✅ Trainings loaded:", trainings.value); // <-- and here

  await fetchMyRegistrations();
  await fetchBookmarks();
  startAllQRCountdowns();

  buildEvents();
  setupCalendarDOM();

  setInterval(() => fetchTrainings(orgID), 30000);
});

async function fetchTrainings() {
  try {
    const response = await axios.get(import.meta.env.VITE_API_BASE_URL +"/trainings");
    trainings.value = response.data;
  } catch (error) {
    console.error("Error fetching trainings:", error);
    addToast("FAILED TO LOAD TRAININGS", "error");
  }
}

onMounted(() => {
  fetchTrainings();
});

watch([trainings, myRegistrations], async () => {
  buildEvents();
  await nextTick();
  setupCalendarDOM();
});
</script>

<template>
  <div>
    <!-- OVERLAY -->
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/40 z-30"
      @click="$emit('close')"
    ></div>

    <!-- SIDEBAR -->
    <aside
      :class="[
        'bg-white shadow-lg rounded-l-lg p-4 transition-transform duration-300 fixed top-0 right-0 h-full z-40 w-80 flex flex-col',
        isOpen ? 'translate-x-0' : 'translate-x-full',
      ]"
    >
      <!-- Close Button -->
      <button
        class="absolute top-4 left-4 text-gray-600 hover:text-gray-900"
        @click="$emit('close')"
      >
        ✕
      </button>

      <!-- Sidebar Content -->
      <div class="flex flex-col items-center gap-4 mt-12 overflow-y-auto">
        <!-- Calendar -->
        <calendar-date
          ref="calendarRef"
          first-day-of-week="0"
          class="cally bg-base-100 border border-base-300 shadow rounded-box p-3 flex-shrink-0"
        >
          <calendar-month>
            <template #day="{ date }">
              <div
                class="w-8 h-8 flex items-center justify-center rounded-full cursor-pointer"
                :class="{
                  'border-2 border-blue-500 bg-blue-50':
                    toISODate(date) === selectedDate,
                  'bg-blue-100 text-blue-700 font-semibold':
                    events[toISODate(date)],
                  'bg-gray-200 text-gray-600': !events[toISODate(date)],
                }"
                @click="onDayClick(date)"
              >
                {{ formatDate(date) }}
              </div>
            </template>
          </calendar-month>
        </calendar-date>

        <!-- Events Panel -->
        <div class="bg-white w-full max-w-[250px] h-72 overflow-y-auto">
          <h1 class="text-lg font-semibold mb-1">Upcoming Events</h1>
          <h2 class="mb-2 text-sm text-gray-600">
            on
            <span class="font-medium">{{
              selectedDate || "Select a date"
            }}</span>
          </h2>

          <div v-if="dayEvents.length === 0" class="text-gray-500 text-sm">
            No events scheduled
          </div>

          <div v-else class="flex flex-col gap-2">
            <div
              v-for="(event, i) in dayEvents"
              :key="i"
              class="bg-gray-100 p-2 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 break-words flex justify-between items-center"
              @click="openModal(event)"
            >
              <div>
                <h3 class="font-semibold text-sm">{{ event.title }}</h3>
                <p class="text-xs text-gray-600">{{ event.organization }}</p>
              </div>
              <span
                class="text-[10px] text-white px-2 py-1 rounded-full"
                :class="
                  event.type === 'training' ? 'bg-blue-500' : 'bg-green-500'
                "
              >
                {{ event.type === "training" ? "Training" : "Career" }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Floating Calendar Button -->
    <button
      v-if="!isOpen"
      class="fixed bottom-6 right-6 bg-dark-slate text-white p-3 rounded-full shadow-lg z-50"
      @click="$emit('open')"
    >
      <slot name="button">
        <!-- Default calendar icon -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path
            d="M16.75 3.56V2C16.75 1.59 16.41 1.25 16 1.25C15.59 1.25 15.25 1.59 15.25 2V3.5H8.74999V2C8.74999 1.59 8.40999 1.25 7.99999 1.25C7.58999 1.25 7.24999 1.59 7.24999 2V3.56C4.54999 3.81 3.23999 5.42 3.03999 7.81C3.01999 8.1 3.25999 8.34 3.53999 8.34H20.46C20.75 8.34 20.99 8.09 20.96 7.81C20.76 5.42 19.45 3.81 16.75 3.56Z"
            fill="white"
          />
          <path
            d="M20 9.84003H4C3.45 9.84003 3 10.29 3 10.84V17C3 20 4.5 22 8 22H16C19.5 22 21 20 21 17V10.84C21 10.29 20.55 9.84003 20 9.84003Z"
            fill="white"
          />
        </svg>
      </slot>
    </button>
    <div>
      <!-- 🟦 Training Modal -->
      <dialog v-if="selectedTraining" open class="modal sm:modal-middle">
        <div class="modal-box max-w-3xl relative font-poppins">
          <!-- Close button -->
          <button
            class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
            @click="closeModal"
          >
            ✕
          </button>

          <!-- Training Details -->
          <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization: {{ selectedTraining.organization }}
          </p>
          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedTraining)"
            >
              {{
                bookmarkedPosts[selectedTraining.trainingID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Register / Unregister -->
            <button
              class="btn btn-sm text-white"
              :class="
                registeredPosts[selectedTraining.trainingID]
                  ? 'bg-gray-500'
                  : 'bg-customButton'
              "
              @click.stop="toggleRegister(selectedTraining)"
            >
              {{
                registeredPosts[selectedTraining.trainingID]
                  ? "Unregister"
                  : "Register"
              }}
            </button>
          </div>

          <p><strong>Mode:</strong> {{ selectedTraining.Mode }}</p>
          <!-- Description -->
          <p>
            <strong>Description: </strong>{{ selectedTraining.description }}
          </p>

          <!-- Conditional display: Online or On-site -->
          <p v-if="selectedTraining.Mode?.toLowerCase() === 'online'">
            <strong>Link:</strong>
            <a
              :href="selectedTraining.trainingLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedTraining.trainingLink }}
            </a>
          </p>
          <p v-else-if="selectedTraining.Mode?.toLowerCase() === 'on-site'">
            <strong>Location:</strong> {{ selectedTraining.location }}
          </p>

          <p>
            <strong>Schedule:</strong>
            {{ formatDate(selectedTraining.date) }} at
            {{ selectedTraining.time }}
          </p>

          <!-- QR code if registered -->
          <div
            v-if="myRegistrations.has(selectedTraining.trainingID)"
            class="mt-4 text-center"
          >
            <div
              v-if="
                selectedTraining.attendance_key &&
                new Date(selectedTraining.end_time) > new Date()
              "
              class="text-center flex flex-col items-center justify-center"
            >
              <qrcode-vue
                :value="selectedTraining.attendance_key"
                :size="120"
              />
              <p class="text-sm text-gray-600 mt-1">
                Expires in:
                {{
                  qrCountdowns[selectedTraining.trainingID] ||
                  formatDateTime(selectedTraining.end_time)
                }}
              </p>
            </div>
            <div v-else>
              <p class="text-sm text-gray-500">
                QR code not yet generated or expired.
              </p>
            </div>
          </div>
        </div>
      </dialog>

      <!-- 🟩 Career Modal -->
      <dialog v-if="selectedPost" open class="modal sm:modal-middle">
        <div class="modal-box max-w-3xl relative font-poppins">
          <!-- Close button -->
          <button
            class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
            @click="closeModal"
          >
            ✕
          </button>

          <!-- Career Details -->
          <h2 class="text-xl font-bold mb-2">{{ selectedPost.position }}</h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization: {{ organizations[selectedPost.organizationID] }}
          </p>

          <!-- Buttons -->
          <div class="my-4 flex justify-end gap-2">
            <!-- Bookmark -->
            <button
              class="btn btn-outline btn-sm"
              @click="toggleBookmark(selectedPost)"
            >
              {{
                bookmarkedPosts[selectedPost.careerID]
                  ? "Bookmarked"
                  : "Bookmark"
              }}
            </button>

            <!-- Apply / Cancel -->
            <button
              v-if="!appliedPosts[selectedPost.careerID]"
              class="btn btn-sm bg-customButton text-white"
              @click="openApplyModal(selectedPost)"
            >
              Apply
            </button>

            <button
              v-else
              class="btn btn-sm bg-gray-500 text-white"
              @click="cancelApplication(selectedPost)"
            >
              Cancel
            </button>
          </div>

          <!-- Career Info -->
          <p>
            <strong>Details:</strong>
            {{ selectedCareer.detailsAndInstructions }}
          </p>
          <p>
            <strong>Qualifications:</strong> {{ selectedCareer.qualifications }}
          </p>
          <p>
            <strong>Requirements:</strong> {{ selectedCareer.requirements }}
          </p>
          <p>
            <strong>Application Address:</strong>
            {{ selectedCareer.applicationLetterAddress }}
          </p>
          <p>
            <strong>Deadline:</strong>
            {{ formatDate(selectedCareer.deadlineOfSubmission) }}
          </p>

          <!-- Interview Schedule -->
          <p v-if="selectedCareer.date && selectedCareer.time">
            <strong>Interview Schedule:</strong>
            {{ formatDate(selectedCareer.date) }} at
            {{ formatTime(selectedCareer.time) }}
          </p>

          <!-- Interview Mode -->
          <p v-if="selectedCareer.mode">
            <strong>Mode:</strong> {{ selectedCareer.mode }}
          </p>

          <!-- Conditional display based on mode -->
          <p
            v-if="
              selectedCareer.mode &&
              selectedCareer.mode.toLowerCase() === 'online' &&
              selectedCareer.interviewLink
            "
          >
            <strong>Link:</strong>
            <a
              :href="selectedCareer.interviewLink"
              target="_blank"
              class="text-blue-500 underline"
            >
              {{ selectedCareer.interviewLink }}
            </a>
          </p>

          <p
            v-if="
              selectedCareer.mode &&
              selectedCareer.mode.toLowerCase() === 'on-site' &&
              selectedCareer.interviewLocation
            "
          >
            <strong>Location:</strong> {{ selectedCareer.interviewLocation }}
          </p>

          <!-- Recommended Trainings -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-3">Recommended Trainings</h3>
            <div
              class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
              style="scrollbar-width: thin"
            >
              <div
                v-for="post in posts.filter((p) => p.type === 'training')"
                :key="post.trainingID"
                class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
                @click.stop="openModal(post)"
              >
                <h4 class="font-semibold text-sm leading-snug mb-1">
                  {{ post.title }}
                </h4>
                <p class="text-[11px] text-gray-600 truncate">
                  {{ organizations[post.organizationID] }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </dialog>
      <!-- Apply Modal -->
      <dialog v-if="applyModalOpen" open class="modal sm:modal-middle">
        <div class="modal-box max-w-lg relative font-poppins">
          <button
            class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
            @click="closeApplyModal"
          >
            ✕
          </button>

          <h2 class="text-xl font-bold mb-4">
            Apply for {{ selectedPost?.position }}
          </h2>

          <form @submit.prevent="submitApplication">
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1"
                >Upload PDF Requirements</label
              >
              <input
                type="file"
                accept="application/pdf"
                @change="handleFileUpload"
                required
                class="file-input file-input-bordered w-full"
              />
            </div>

            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="btn btn-outline btn-sm"
                @click="closeApplyModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn bg-customButton hover:bg-dark-slate text-white btn-sm"
              >
                Submit
              </button>
            </div>
          </form>
        </div>
      </dialog>
    </div>
  </div>
</template>
