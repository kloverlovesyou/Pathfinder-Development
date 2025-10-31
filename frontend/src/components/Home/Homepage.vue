<script setup>
import { ref, onMounted, nextTick, reactive } from "vue";
import axios from "axios";
const registeredPosts = reactive({});

const myRegistrations = ref(new Set());
async function fetchMyRegistrations() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/registrations", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Fill registeredPosts
    res.data.forEach((r) => {
      registeredPosts[r.trainingID] = { registrationID: r.registrationID };
    });
  } catch (err) {
    console.error("Failed to fetch registrations:", err);
  }
}

onMounted(fetchMyRegistrations);

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
async function toggleRegister(training) {
  const id = training.trainingID;

  if (registeredPosts[id]) {
    // Unregister
    await axios.delete(
      `http://127.0.0.1:8000/api/registrations/${registeredPosts[id].registrationID}`
    );
    registeredPosts[id] = null; // or use: delete registeredPosts[id]
  } else {
    // Register
    const res = await axios.post("http://127.0.0.1:8000/api/registrations", {
      trainingID: id,
    });
    registeredPosts[id] = { registrationID: res.data.data.registrationID };
  }
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

  // Highlight event days on render
  calendar.addEventListener("render", () => {
    calendar.querySelectorAll("[data-date]").forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("event-day");
      if (events.value[dateStr]) {
        el.classList.add("event-day");
      }
    });
  });

  // Handle day clicks
  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);
  });
});
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
            @click="openModal(post)"
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

      <!-- OVERLAY -->
      <div
        v-if="isSidebarOpen"
        class="fixed inset-0 bg-black/40 z-30"
        @click="isSidebarOpen = false"
      ></div>

      <!-- SIDEBAR -->
      <aside
        :class="[
          'bg-white shadow-lg rounded-l-lg p-4 transition-transform duration-300 fixed top-0 right-0 h-full z-40 w-80 flex flex-col',
          isSidebarOpen ? 'translate-x-0' : 'translate-x-full',
        ]"
      >
        <!-- Close Button -->
        <button
          class="absolute top-4 left-4 text-gray-600 hover:text-gray-900"
          @click="isSidebarOpen = false"
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
              <template #day="{ date, label }">
                <div
                  class="w-8 h-8 flex items-center justify-center rounded-full"
                  :data-date="date"
                  :class="{
                    'bg-blue-100 text-blue-700 font-semibold': events[date],
                    'bg-gray-200 text-gray-600': !events[date],
                  }"
                  @click="showEvents(date)"
                >
                  {{ label }}
                </div>
              </template>
            </calendar-month>
          </calendar-date>

          <!-- Events Panel -->
          <div
            class="bg-white w-full max-w-[250px] h-72 overflow-y-auto border rounded-lg p-3"
          >
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
                  <h3 class="font-semibold text-sm">
                    {{ event.title }}
                  </h3>
                  <p class="text-xs text-gray-600">
                    {{ event.organization }}
                  </p>
                </div>

                <!-- ✅ Event Type Badge -->
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
        v-if="!isSidebarOpen"
        class="fixed bottom-6 right-6 bg-dark-slate text-white p-3 rounded-full shadow-lg z-50"
        @click="isSidebarOpen = true"
      >
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M16.75 3.56V2C16.75 1.59 16.41 1.25 16 1.25C15.59 1.25 15.25 1.59 15.25 2V3.5H8.74999V2C8.74999 1.59 8.40999 1.25 7.99999 1.25C7.58999 1.25 7.24999 1.59 7.24999 2V3.56C4.54999 3.81 3.23999 5.42 3.03999 7.81C3.01999 8.1 3.25999 8.34 3.53999 8.34H20.46C20.75 8.34 20.99 8.09 20.96 7.81C20.76 5.42 19.45 3.81 16.75 3.56Z"
            fill="white"
          />
          <path
            d="M20 9.84003H4C3.45 9.84003 3 10.29 3 10.84V17C3 20 4.5 22 8 22H16C19.5 22 21 20 21 17V10.84C21 10.29 20.55 9.84003 20 9.84003ZM9.21 18.21C9.16 18.25 9.11 18.3 9.06 18.33C9 18.37 8.94 18.4 8.88 18.42C8.82 18.45 8.76 18.47 8.7 18.48C8.63 18.49 8.57 18.5 8.5 18.5C8.37 18.5 8.24 18.47 8.12 18.42C7.99 18.37 7.89 18.3 7.79 18.21C7.61 18.02 7.5 17.76 7.5 17.5C7.5 17.24 7.61 16.98 7.79 16.79C7.89 16.7 7.99 16.63 8.12 16.58C8.3 16.5 8.5 16.48 8.7 16.52C8.76 16.53 8.82 16.55 8.88 16.58C8.94 16.6 9 16.63 9.06 16.67C9.11 16.71 9.16 16.75 9.21 16.79C9.39 16.98 9.5 17.24 9.5 17.5C9.5 17.76 9.39 18.02 9.21 18.21ZM12.71 18.21C12.52 18.39 12.26 18.5 12 18.5C11.74 18.5 11.48 18.39 11.29 18.21C11.11 18.02 11 17.76 11 17.5C11 17.24 11.11 16.98 11.29 16.79C11.66 16.42 12.34 16.42 12.71 16.79C12.89 16.98 13 17.24 13 17.5C13 17.76 12.89 18.02 12.71 18.21ZM16.21 18.21C16.02 18.39 15.76 18.5 15.5 18.5C15.24 18.5 14.98 18.39 14.79 18.21C14.61 18.02 14.5 17.76 14.5 17.5C14.5 17.24 14.61 16.98 14.79 16.79C15.16 16.42 15.84 16.42 16.21 16.79C16.39 16.98 16.5 17.24 16.5 17.5C16.5 17.76 16.39 18.02 16.21 18.21Z"
            fill="white"
          />
        </svg>
      </button>
    </div>

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
</style>
