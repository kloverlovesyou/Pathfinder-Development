<script setup>
import { ref, onMounted, nextTick, reactive } from "vue";
import axios from "axios";

import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const calendarOpen = ref(false);

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

const registeredPosts = reactive({}); // stores registered trainings

async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/registrations", {
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
        `http://127.0.0.1:8000/api/registrations/${registrationID}`,
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
        "http://127.0.0.1:8000/api/registrations",
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

const appliedPosts = ref({});
const isSidebarOpen = ref(false);
const bookmarkedPosts = ref({});

function cancelApplication(post) {
  const id = post.careerID;
  appliedPosts.value[id] = false;
}

function toggleBookmark(post) {
  const id = post.trainingID || post.careerID;
  bookmarkedPosts.value[id] = !bookmarkedPosts.value[id];
}

const selectedTraining = ref(null);

function openTrainingModal(training) {
  selectedTraining.value = training;
}

function closeTrainingModal() {
  selectedTraining.value = null;
}

// ✅ User’s registered/applied posts (just keep IDs)
const registeredTrainings = ref([1]); // trainingID
const appliedCareers = ref([2]); // careerID

// Helpers
function isTraining(post) {
  return post && (post.type === "training" || post.trainingID);
}

const isRegisteredOrApplied = (post) => {
  if (isTraining(post)) {
    return registeredTrainings.value.includes(post.trainingID);
  } else {
    return appliedCareers.value.includes(post.careerID);
  }
};

// Modal
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
      `http://127.0.0.1:8000/api/calendar/${user.applicantID}`,
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
</script>

<template>
  <div class="font-poppins">
    <!-- Layout wrapper -->
    <div class="relative font-poppins min-h-screen flex">
      <!-- MAIN CONTENT -->
      <main
        class="flex-1 bg-white m-3 px-4 rounded-lg flex flex-col min-h-0 overflow-hidden"
      >
        <!-- Sticky Header -->
        <div
          class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm"
        >
          <h2 class="text-lg font-bold">Career</h2>
          <h2 class="text-2xl font-bold mb-2">Match Recommendation</h2>
        </div>

        <!-- Scrollable Posts -->
        <div class="flex-1 overflow-y-auto space-y-4 pb-4 pt-4">
          <div
            v-for="post in posts"
            :key="post.trainingID || post.careerID"
            class="p-4 bg-blue-gray rounded-lg relative cursor-pointer hover:bg-gray-300 transition"
            @click="openCareerModal(post)"
          >
            <h3 class="font-semibold text-lg">
              {{ isTraining(post) ? post.title : post.position }}
            </h3>
            <p class="text-gray-600 text-sm">
              {{ organizations[post.organizationID] }}
            </p>
          </div>
        </div>
      </main>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <!-- 🟦 Training Modal -->
    <dialog v-if="selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeTrainingModal"
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
        <p><strong>Description: </strong>{{ selectedTraining.description }}</p>

        <!-- Conditional display: Online or On-site -->
        <p v-if="selectedTraining.Mode.toLowerCase() === 'online'">
          <strong>Link:</strong>
          <a
            :href="selectedTraining.trainingLink"
            target="_blank"
            class="text-blue-500 underline"
          >
            {{ selectedTraining.trainingLink }}
          </a>
        </p>
        <p v-else-if="selectedTraining.Mode.toLowerCase() === 'on-site'">
          <strong>Location:</strong> {{ selectedTraining.location }}
        </p>

        <p>
          <strong>Schedule:</strong>
          {{ formatDate(selectedTraining.date) }} at {{ selectedTraining.time }}
        </p>
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
              bookmarkedPosts[selectedPost.careerID] ? "Bookmarked" : "Bookmark"
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
          <strong>Details:</strong> {{ selectedCareer.detailsAndInstructions }}
        </p>
        <p>
          <strong>Qualifications:</strong> {{ selectedCareer.qualifications }}
        </p>
        <p><strong>Requirements:</strong> {{ selectedCareer.requirements }}</p>
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
</template>

<style>
.event-day {
  background-color: #fde68a;
  border-radius: 9999px;
  font-weight: bold;
}
.selected-day {
  background-color: #3b82f6;
  color: white;
  border-radius: 9999px;
}

.today {
  border: 2px solid #4caf50;
  background-color: #e8f5e9;
  border-radius: 50%;
}
</style>
