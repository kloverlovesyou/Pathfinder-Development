<script setup>
import { ref, onMounted, nextTick } from "vue";
import "cally";

const posts = ref([
  // Mock data only for frontend
  {
    trainingID: 1,
    title: "Professional development in emerging technologies",
    schedule: "2025-11-24T13:30:00",
    organizationID: 1,
    location: "Main Hall",
    trainingLink: "#",
    mode: "Onsite",
    description: "Learn about emerging technologies.",
  },
  {
    trainingID: 2,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
  },
  {
    trainingID: 6,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
  },
  {
    trainingID: 7,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
  },
  {
    careerID: 3,
    position: "Software Engineer",
    deadlineOfSubmission: "2025-07-24",
    organizationID: 1,
    detailsAndInstructions: "Submit resume and portfolio.",
    qualifications: "BS in Computer Science",
    requirements: "Proficiency in Vue.js",
    applicationLetterAddress: "hr@techcorp.com",
  },
  {
    careerID: 4,
    position: "Software Engineer",
    deadlineOfSubmission: "2025-07-24",
    organizationID: 1,
    detailsAndInstructions: "Submit resume and portfolio.",
    qualifications: "BS in Computer Science",
    requirements: "Proficiency in Vue.js",
    applicationLetterAddress: "hr@techcorp.com",
  },
  {
    careerID: 5,
    position: "Software Engineer",
    deadlineOfSubmission: "2025-07-24",
    organizationID: 1,
    detailsAndInstructions: "Submit resume and portfolio.",
    qualifications: "BS in Computer Science",
    requirements: "Proficiency in Vue.js",
    applicationLetterAddress: "hr@techcorp.com",
  },
]);

// Mock org names
const organizations = {
  1: "Tech Corp",
  2: "Future Academy",
};

// ✅ User’s registered/applied posts (just keep IDs)
const registeredTrainings = ref([1]); // trainingID
const appliedCareers = ref([2]); // careerID

// Helpers
const isTraining = (post) => !!post.trainingID;

const isRegisteredOrApplied = (post) => {
  if (isTraining(post)) {
    return registeredTrainings.value.includes(post.trainingID);
  } else {
    return appliedCareers.value.includes(post.careerID);
  }
};

// Calendar + events
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

// Modal
const selectedPost = ref(null);
function openModal(post) {
  selectedPost.value = post;
}
function closeModal() {
  selectedPost.value = null;
}

// Build events map
function buildEvents() {
  events.value = {};
  posts.value.forEach((post) => {
    const date = post.trainingID
      ? post.schedule.split("T")[0]
      : post.deadlineOfSubmission;

    if (!events.value[date]) events.value[date] = [];
    events.value[date].push(post);
  });
}

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

onMounted(async () => {
  await nextTick();
  buildEvents();

  const calendar = calendarRef.value;
  const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

  // Highlight event days and today
  calendar.addEventListener("render", () => {
    calendar.querySelectorAll("[data-date]").forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("event-day", "today");

      if (events.value[dateStr]) el.classList.add("event-day");
      if (dateStr === today) el.classList.add("today");
    });
  });

  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);

    calendar
      .querySelectorAll("[data-date]")
      .forEach((el) => el.classList.remove("selected-day"));
    const selectedEl = calendar.querySelector(`[data-date="${pickedDate}"]`);
    if (selectedEl) selectedEl.classList.add("selected-day");
  });

  // ✅ Auto-select today's date to show events
  calendar.value = today; // Set the <calendar-date> selected value
  showEvents(today); // Load today's events into the panel

  // Manually trigger "change" event to simulate user selecting today
  const event = new Event("change", { bubbles: true });
  calendar.dispatchEvent(event);
});

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

const applyModalOpen = ref(false);
const uploadedFile = ref(null);

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

  console.log("Submitting application for:", selectedPost.value);
  console.log("Uploaded file:", uploadedFile.value);

  // TODO: send file + post info to backend with FormData
  // Example:
  // const formData = new FormData();
  // formData.append("file", uploadedFile.value);
  // formData.append("careerID", selectedPost.value.careerID);
  // await axios.post("/api/apply", formData);

  alert("Application submitted successfully!");
  closeApplyModal();
}
</script>

<template>
  <div class="font-poppins">
    <!-- Layout wrapper -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] min-h-screen gap-3">
      <!-- Career & Training Cards -->
      <main
        class="bg-white m-3 px-4 rounded-lg flex flex-col max-h-[60vh] overflow-y-auto lg:max-h-none lg:overflow-visible"
      >
        <div class="sticky top-0 bg-white z-10 pt-4 px-4">
          <h2 class="text-lg font-bold -mb-1">Career</h2>
          <h2 class="text-2xl font-bold mb-4">Match Recommendation</h2>
        </div>

        <!-- Scrollable Posts Container -->
        <div class="space-y-4 flex-1 pb-2">
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

      <!-- Calendar & Events -->
      <aside class="bg-white m-3 p-4 rounded-lg h-full">
        <!-- Wrapper for calendar + events -->
        <div
          class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-center gap-4"
        >
          <!-- Calendar -->
          <calendar-date
            ref="calendarRef"
            first-day-of-week="0"
            class="cally bg-base-100 border border-base-300 shadow rounded-box p-3 flex-shrink-0"
          >
            <calendar-month>
              <template slot="day" date label>
                <div
                  class="w-8 h-8 flex items-center justify-center rounded-full"
                  :data-date="date"
                >
                  {{ label }}
                </div>
              </template>
            </calendar-month>
          </calendar-date>

          <!-- Events Panel -->
          <div class="bg-white w-full max-w-[250px] h-72 overflow-y-auto">
            <h1 class="text-lg font-semibold">Upcoming Events</h1>
            <h2 class="mb-2">
              on <span>{{ selectedDate || "Select a date" }}</span>
            </h2>

            <div v-if="dayEvents.length === 0" class="text-gray-700">
              No events scheduled
            </div>

            <div v-else class="flex flex-col gap-2">
              <div
                v-for="(event, i) in dayEvents"
                :key="i"
                @click="openModal(event)"
                class="bg-gray-100 p-2 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 break-words flex justify-between items-center"
              >
                <div>
                  <h3 class="font-semibold text-sm">
                    {{ isTraining(event) ? event.title : event.position }}
                  </h3>
                  <p class="text-xs text-gray-600">
                    {{ organizations[event.organizationID] }}
                  </p>
                </div>

                <!-- ✅ Badge -->
                <span
                  v-if="isRegisteredOrApplied(event)"
                  class="text-[10px] text-white px-2 py-1 rounded-full"
                  :class="isTraining(event) ? 'bg-blue-500' : 'bg-green-500'"
                >
                  {{ isTraining(event) ? "Registered" : "Applied" }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </div>

    <!-- Post Details Modal -->
    <dialog v-if="selectedPost" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>

        <!-- Training -->
        <div v-if="isTraining(selectedPost)">
          <h2 class="text-xl font-bold mb-2">{{ selectedPost.title }}</h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization: {{ organizations[selectedPost.organizationID] }}
          </p>
          <div class="my-4 flex justify-end gap-2">
            <button
              class="btn btn-outline btn-sm"
              @click="bookmarkPost(selectedPost)"
            >
              Bookmark
            </button>
            <button
              class="btn bg-customButton btn-sm text-white"
              @click="registerTraining(selectedPost)"
            >
              Register
            </button>
          </div>
          <p>
            <strong>Mode:</strong> {{ selectedPost.mode || "Not specified" }}
          </p>

          <p><strong>Description:</strong> {{ selectedPost.description }}</p>
          <p>
            <strong>Schedule:</strong>
            {{ formatDateTime(selectedPost.schedule) }}
          </p>
          <p><strong>Location:</strong> {{ selectedPost.location }}</p>
        </div>

        <!-- Career -->
        <div v-else>
          <h2 class="text-xl font-bold mb-2">{{ selectedPost.position }}</h2>
          <p class="text-sm text-gray-600 mb-2">
            Organization: {{ organizations[selectedPost.organizationID] }}
          </p>
          <div class="my-4 flex justify-end gap-2">
            <button
              class="btn btn-outline btn-sm"
              @click="bookmarkPost(selectedPost)"
            >
              Bookmark
            </button>
            <button
              class="btn btn-sm bg-customButton text-white"
              @click="openApplyModal(selectedPost)"
            >
              Apply
            </button>
          </div>
          <p>
            <strong>Details:</strong> {{ selectedPost.detailsAndInstructions }}
          </p>
          <p>
            <strong>Qualifications:</strong> {{ selectedPost.qualifications }}
          </p>
          <p><strong>Requirements:</strong> {{ selectedPost.requirements }}</p>
          <p>
            <strong>Application Address:</strong>
            {{ selectedPost.applicationLetterAddress }}
          </p>
          <p>
            <strong>Deadline:</strong>
            {{ formatDate(selectedPost.deadlineOfSubmission) }}
          </p>
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
              Submit Application
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
