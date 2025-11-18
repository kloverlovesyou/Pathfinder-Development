<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import QrcodeVue from "qrcode.vue"; // make sure to import this if using QR

const registeredPosts = reactive({}); // stores registered trainings

async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

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
const applications = ref([]);
const posts = ref([]); // never undefined
const organizations = ref({}); // never undefined

const fetchApplications = async (applicantID) => {
  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL +
        `/applications?applicantID=${applicantID}`
    );
    applications.value = response.data;
  } catch (error) {
    console.error("Failed to fetch applications:", error);
  }
};
onMounted(fetchMyRegistrations);
// --- Career events for applications the user applied for ---

async function fetchCareerEvents() {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    const token = localStorage.getItem("token");
    if (!user || !token) return;

    // Call the API that returns applications with interview schedule
    const { data: apps } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    // Filter only applications that have an interview schedule
    const careerEvents = apps
      .filter((app) => app.interviewSchedule) // skip applications without interviews
      .map((app) => ({
        id: app.applicationID,
        careerID: app.careerID,
        title: app.career?.position,
        date: new Date(app.interviewSchedule).toISOString().split("T")[0],
        type: "career",
        interviewSchedule: app.interviewSchedule,
        interviewMode: app.interviewMode,
        interviewLink: app.interviewLink,
        interviewLocation: app.interviewLocation,
        organization: app.career?.organization,
      }));

    // Merge career events into your existing events map
    careerEvents.forEach((event) => {
      if (!events.value[event.date]) events.value[event.date] = [];
      events.value[event.date].push(event);
    });

    console.log("📅 Career events merged:", careerEvents);
  } catch (error) {
    console.error("❌ Failed to fetch career events:", error);
  }
}

const qrCodeValue = computed(() => {
  if (!selectedTraining.value) return "";
  return `${import.meta.env.VITE_API_BASE_URL}/attendance?trainingID=${
    selectedTraining.value.trainingID
  }&key=${selectedTraining.value.attendance_key}`;
});
// 🔹 Fetch complete career details for the modal
async function fetchCareerDetails(careerID) {
  const token = localStorage.getItem("token");

  if (!token) {
    console.error("⚠️ No token found — user may not be logged in.");
    return null;
  }

  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/careers/${careerID}`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
        },
      }
    );

    console.log("✅ Career details fetched:", response.data);
    return response.data;
  } catch (error) {
    console.error("❌ Failed to fetch career details:", error);
    return null;
  }
}
async function fetchInterviews() {
  try {
    const res = await axios.get("/api/interviews");
    careerEvents.value = res.data;
  } catch (err) {
    console.error("Error fetching interviews:", err);
  }
}
function toggleBookmark(post) {
  const id = post.trainingID || post.careerID;
  bookmarkedPosts.value[id] = !bookmarkedPosts.value[id];
}
const bookmarkedPosts = ref({});

// Toggle registration
async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) return alert("Please log in first.");

  // If already registered -> unregister
  if (registeredPosts[training.trainingID]) {
    try {
      const registrationID =
        registeredPosts[training.trainingID].registrationID;
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

      registeredPosts[training.trainingID] = {
        registrationID: res.data.registrationID,
      };

      console.log(`✅ Registered for ${training.title}`);
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

async function openModal(post) {
  console.log("Opening modal for:", post); // ✅ debug line

  if (post.type === "training") {
    selectedTraining.value = post; // 🟦 training modal
    selectedPost.value = null;
  } else if (post.type === "career") {
    const careerDetails = await fetchCareerDetails(post.careerID);

    const merged = {
      ...post, // first object: date, mode, link
      ...careerDetails, // second object: details, organization, deadline
    };

    selectedPost.value = merged;
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
  await fetchCareerEvents(); // add career events
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
  await fetchInterviews();
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
  qrCodeValue.value =
    import.meta.env.VITE_API_BASE_URL +
    `/attendance/submit?trainingID=${training.trainingID}&key=${training.attendance_key}`;
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
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/trainings"
    );
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
              @click="
                () => {
                  console.log('Clicked event:', event);
                  openModal(event);
                }
              "
            >
              <div>
                <h3 class="font-semibold text-sm">
                  {{
                    event.title ||
                    (event.type === "career"
                      ? selectedPost.title || "Career Event"
                      : "Training Event")
                  }}
                </h3>

                <!-- Optional: show interview date/time for career events -->
                <p
                  v-if="event.type === 'career' && event.date"
                  class="text-xs text-gray-500"
                >
                  {{ event.organization ?? "" }}
                </p>
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

        <p><strong>Mode:</strong> {{ selectedTraining.mode }}</p>
        <!-- Description -->
        <p><strong>Description: </strong>{{ selectedTraining.description }}</p>

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

        <!-- QR Code Section -->
        <div
          v-if="selectedTraining.attendance_key"
          class="mt-6 text-center border-t pt-4"
        >
          <p class="text-sm font-semibold mb-2">
            Scan this QR Code for Attendance
          </p>

          <QrcodeVue
            :value="qrCodeValue"
            :size="200"
            level="H"
            class="mx-auto"
          />

          <p class="text-gray-500 text-xs mt-2">
            Expires at:
            {{ formatDateTime(selectedTraining.end_time) }}
          </p>
        </div>

        <!-- No QR yet -->
        <div v-else>
          <div class="divider"></div>
          <p class="text-sm text-gray-500 text-center">
            QR Code not available yet or has expired.
          </p>
        </div>
      </div>
    </dialog>

    <!-- 🟩 Career Modal -->
    <!-- TEST MODAL -->
    <div
      v-if="selectedPost"
      style="
        position: fixed;
        inset: 0;

        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      "
    >
      <div class="modal-box relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>

        <!-- Career Details -->
        <h2 class="text-xl font-bold mb-2">{{ selectedPost.title }}</h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization:
          {{ selectedPost.organization || "Unknown Organization" }}
        </p>

        <p>
          <strong>Details:</strong>
          {{ selectedPost?.detailsAndInstructions || "N/A" }}
        </p>
        <p>
          <strong>Qualifications:</strong>
          {{ selectedPost?.qualifications || "N/A" }}
        </p>

        <p>
          <strong>Requirements:</strong>
          {{ selectedPost.requirements || "N/A" }}
        </p>
        <p>
          <strong>Application Address:</strong>
          {{ selectedPost.applicationLetterAddress || "N/A" }}
        </p>
        <p>
          <strong>Deadline of Submission:</strong>
          {{ formatDate(selectedPost.deadlineOfSubmission) || "N/A" }}
        </p>
        <div class="divider"></div>
        <p v-if="selectedPost.interviewSchedule">
          <strong>Interview Schedule:</strong>
          {{ formatDateTime(selectedPost.interviewSchedule) }}
        </p>

        <p v-if="selectedPost.interviewMode">
          <strong>Interview Mode:</strong> {{ selectedPost.interviewMode }}
        </p>

        <p
          v-if="
            selectedPost.interviewMode?.toLowerCase() === 'online' &&
            selectedPost.interviewLink
          "
        >
          <strong>Interview Link: </strong>
          <a :href="selectedPost.interviewLink" target="_blank">{{
            selectedPost.interviewLink
          }}</a>
        </p>

        <p
          v-if="
            selectedPost.interviewMode?.toLowerCase() === 'on-site' &&
            selectedPost.interviewLocation
          "
        >
          <strong>Location:</strong> {{ selectedPost.interviewLocation }}
        </p>
      </div>
    </div>
  </div>
</template>
