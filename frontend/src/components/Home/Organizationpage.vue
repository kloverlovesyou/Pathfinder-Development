<script setup>
import { ref, onMounted, nextTick, reactive } from "vue";
import axios from "axios";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const registeredTrainings = reactive(new Set());
const bookmarks = reactive(new Set());
const careerBookmarks = ref([]);
const applications = ref([]);
const toasts = ref([]);

onMounted(async () => {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const [bookmarksRes, regRes] = await Promise.all([
      axios.get(import.meta.env.VITE_API_BASE_URL + "/bookmarks", {
        headers: { Authorization: `Bearer ${token}` },
      }),
      axios.get(import.meta.env.VITE_API_BASE_URL + "/registrations", {
        headers: { Authorization: `Bearer ${token}` },
      }),
    ]);

    // Add all bookmarked trainings
    bookmarksRes.data.forEach((b) => bookmarks.add(b.trainingID));

    // Add all registered trainings
    regRes.data.forEach((r) => registeredTrainings.add(r.trainingID));
  } catch (err) {
    console.error("Error loading data:", err);
    toast.error("Failed to load training data");
  }
});

// ✅ Toggle Bookmark
async function toggleTrainingBookmark(training) {
  const token = localStorage.getItem("token");
  if (!token) return toast.error("Please log in first");

  try {
    if (!bookmarks.has(training.trainingID)) {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/bookmarks",
        { trainingID: training.trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarks.add(training.trainingID);
      toast.success("Training bookmarked!");
    } else {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/bookmarks/${training.trainingID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      bookmarks.delete(training.trainingID);
      toast.info("Bookmark removed.");
    }
  } catch (err) {
    console.error("Bookmark toggle failed:", err);
    toast.error("Failed to update bookmark");
  }
}

// ✅ Toggle Register / Unregister
async function toggleRegistration(training) {
  const token = localStorage.getItem("token");
  if (!token) return toast.error("Please log in first");

  try {
    if (!registeredTrainings.has(training.trainingID)) {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/registrations",
        { trainingID: training.trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      registeredTrainings.add(training.trainingID);
      toast.success("Successfully registered!");
    } else {
      // Unregister
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/registrations/${training.trainingID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      registeredTrainings.delete(training.trainingID);
      toast.info("You have unregistered.");
    }
  } catch (err) {
    console.error("Registration toggle failed:", err);
    toast.error("Action failed, please try again.");
  }
}
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  }, 3000);
}

onMounted(async () => {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const [careerBookmarkRes, appRes] = await Promise.all([
      axios.get(import.meta.env.VITE_API_BASE_URL + "/career-bookmarks", {
        headers: { Authorization: `Bearer ${token}` },
      }),
      axios.get(import.meta.env.VITE_API_BASE_URL + "/applications", {
        headers: { Authorization: `Bearer ${token}` },
      }),
    ]);

    careerBookmarks.value = careerBookmarkRes.data.map((b) => b.careerID);
    applications.value = appRes.data.map((a) => a.careerID);
  } catch (err) {
    addToast.error("Failed to load bookmarks or applications.");
    console.error(err);
  }
});

// ✅ Bookmark helpers
const isCareerBookmarked = (careerID) =>
  careerBookmarks.value.includes(careerID);

async function toggleCareerBookmark(career) {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast.warning("Please log in first.");
    return;
  }

  try {
    if (isCareerBookmarked(career.careerID)) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/career-bookmarks/${career.careerID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      careerBookmarks.value = careerBookmarks.value.filter(
        (id) => id !== career.careerID
      );
      addToast.info("Bookmark removed.");
    } else {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/career-bookmarks",
        { careerID: career.careerID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      careerBookmarks.value.push(career.careerID);
      addToast.success("Career bookmarked!");
    }
  } catch (err) {
    addToast.error("Bookmark action failed.");
    console.error(err);
  }
}

// ✅ Application helpers
const hasApplied = (careerID) => applications.value.includes(careerID);

async function applyToCareer(career) {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast.warning("Please log in first.");
    return;
  }

  try {
    await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      { careerID: career.careerID },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    applications.value.push(career.careerID);
    addToast.success("Successfully applied to this career!");
  } catch (err) {
    addToast.error("Application failed. Please try again.");
    console.error(err);
  }
}

const calendarOpen = ref(false);

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

// Modal & sidebar
const isModalOpen = ref(false);
const selectedOrg = ref({
  careers: [],
  trainings: [],
});

const isSidebarOpen = ref(false);
const orgModal = ref(null);
const myRegistrations = ref(new Set());

// Organizations
const organizations = ref([]);

async function openModal(item) {
  console.log("openModal called with item:", item);

  let org = item;

  // ✅ If the item has an organizationID, fetch that organization
  if (item.organizationID) {
    try {
      const response = await axios.get(
        import.meta.env.VITE_API_BASE_URL + `/organizations/${item.organizationID}`
      );
      org = response.data;
      console.log("Fetched organization from backend:", org);
    } catch (error) {
      console.error("Error fetching organization:", error);
      return;
    }
  }

  // ✅ Handle if no organization is found
  if (!org) {
    console.warn("Organization not found for modal:", item);
    return;
  }

  // ✅ Deep copy to avoid reactivity glitches
  selectedOrg.value = JSON.parse(JSON.stringify(org));

  // ✅ Open the modal after data is ready
  await nextTick();
  if (orgModal.value) orgModal.value.showModal();
}

function closeModal() {
  if (orgModal.value && orgModal.value.open) {
    orgModal.value.close();
  }
  selectedOrg.value = {};
}

// Calendar + events
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

// Modal posts
const selectedPost = ref(null);

// Build events map
function buildEvents() {
  events.value = {};

  organizations.value.forEach((org) => {
    // Add trainings
    org.trainings.forEach((training) => {
      const date = training.schedule?.split("T")[0]; // YYYY-MM-DD
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push({
        ...training,
        type: "training",
        organizationID: org.id,
      });
    });

    // Add careers
    org.careers.forEach((career) => {
      const date = career.deadline?.split("T")[0]; // Assuming it's ISO string
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push({
        ...career,
        type: "career",
        organizationID: org.id,
      });
    });
  });
}

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

// Get organization name by ID (for events)
function getOrgNameById(id) {
  const org = organizations.value.find((o) => o.id === id);
  return org ? org.name : "Unknown Organization";
}

function isTraining(event) {
  return event.type === "training";
}

// ✅ Format datetime (with AM/PM)
function formatDateTime(dateStr) {
  if (!dateStr) return "N/A";
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;

  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}
function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", {
    dateStyle: "long",
  });
}

// Format ISO date-time to readable string
function formatSchedule(dt) {
  if (!dt) return "N/A";
  const date = new Date(dt);
  return date.toLocaleString("en-US", {
    weekday: "short", // e.g., "Mon"
    month: "short", // e.g., "Oct"
    day: "numeric", // e.g., 31
    year: "numeric", // e.g., 2025
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

// Fetch organizations from backend
onMounted(async () => {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/organization");
    // Map backend fields to frontend template

    // Map backend fields for frontend usage
    organizations.value = res.data.map((org) => ({
      id: org.organizationID, // ← use organizationID
      name: org.name,
      location: org.location || "N/A",
      website: org.websiteURL || "#",
      logo: org.logo || null,
      careers: (org.careers || []).map((c) => ({
        id: c.careerID, // ← careerID from backend
        position: c.position,
        deadlineOfSubmission: c.deadlineOfSubmission,
        detailsAndInstructions: c.detailsAndInstructions,
        qualifications: c.qualifications,
        requirements: c.requirements,
        applicationLetterAddress: c.applicationLetterAddress,
      })),
      trainings: (org.trainings || []).map((t) => ({
        id: t.trainingID, // ← trainingID from backend
        title: t.title,
        description: t.description,
        schedule: t.schedule,
        end_time: t.end_time,
        mode: t.mode,
        location: t.location,
        trainingLink: t.trainingLink,
        attendance_key: t.attendance_key,
        attendance_expires_at: t.attendance_expires_at,
        qr_generated_at: t.qr_generated_at,
      })),
    }));
    buildEvents();
    await fetchBookmarks();

    // 3️⃣ Fetch user’s bookmarked careers
    await fetchCareerBookmarks();

    // 4️⃣ Fetch user’s applications
    await fetchMyApplications();
  } catch (error) {
    console.error("Error fetching organizations:", error);
  }

  await nextTick();
  console.log("orgModal after onMounted + nextTick:", orgModal.value);
  // Calendar events setup (if you have events)
  const calendar = calendarRef.value;
  const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

  if (calendar) {
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

    // Auto-select today's date
    calendar.value = today;
    showEvents(today);

    const event = new Event("change", { bubbles: true });
    calendar.dispatchEvent(event);
  }
});

const selectedCareer = ref(null);
const selectedTraining = ref(null);
const careerModal = ref(null);
const trainingModal = ref(null);

// === Modal Functions ===
function closeOrgModal() {
  const modal = document.getElementById("orgModal");
  if (modal?.close) modal.close();
  selectedOrg.value = null;
}

// ✅ Open Career Modal (fetch from backend)
// --- ✅ Fetch Career Details ---
async function openCareerModal(career) {
  console.log("Career clicked:", career);
  if (orgModal.value && orgModal.value.open) orgModal.value.close();
  try {
    const token = localStorage.getItem("token");
    if (!token) {
      console.error("❌ No user token found — please log in.");
      return;
    }

    // 🔄 Fetch all applications for the logged-in applicant
    const response = await axios.get(import.meta.env.VITE_API_BASE_URL + "/applications", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // 🔍 Find if the user applied to this career
    const app = response.data.find((a) => a.careerID === career.id);

    if (app) {
      console.log("✅ Application + interview found for this career:", app);
      selectedCareer.value = app;
    } else {
      console.log(
        "ℹ️ No application found for this career — showing basic info."
      );
      selectedCareer.value = career;
    }

    await nextTick();
    if (careerModal.value) careerModal.value.showModal();
  } catch (err) {
    console.error("❌ Error fetching application or interview:", err);
  }
}

// --- ✅ Fetch Training Details ---
async function openTrainingModal(training) {
  console.log("🟨 Training post clicked:", training); // shows data of the clicked post
  if (orgModal.value && orgModal.value.open) orgModal.value.close();
  try {
    const trainingId = training.id || training.trainingID;
    const userToken = localStorage.getItem("token"); // same as career modal

    console.log("🔄 Fetching training details for ID:", trainingId);
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/trainings/${trainingId}`,
      {
        headers: {
          Authorization: `Bearer ${userToken}`,
        },
      }
    );

    console.log("✅ Training details fetched from backend:", response.data);
    selectedTraining.value = response.data;

    await nextTick(); // wait until dialog exists in DOM
    if (trainingModal.value) trainingModal.value.showModal();
  } catch (error) {
    console.error("❌ Error fetching training:", error);
  }
}

// ✅ Close Career/Training Modals
function closeCareerModal() {
  const modal = document.getElementById("careerModal");
  if (modal?.close) modal.close();
  selectedCareer.value = null;
}

function closeTrainingModal() {
  const modal = document.getElementById("trainingModal");
  if (modal?.close) modal.close();
  if (trainingModal.value && trainingModal.value.open)
    trainingModal.value.close();
  selectedTraining.value = null;
}
</script>

<template>
  <div>
    <main class="font-poppins">
      <div class="bg-white m-3 p-4 rounded-lg">
        <!-- Header -->
        <div
          class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm"
        >
          <h2 class="text-2xl font-bold mb-3">Organizations</h2>
        </div>

        <!-- Organization List -->
        <div class="space-y-4">
          <div
            v-for="org in organizations"
            :key="org.id"
            class="flex items-center gap-4 p-4 bg-blue-gray rounded-lg hover:bg-gray-200 transition cursor-pointer"
            @click="openModal(org)"
          >
            <!-- Profile Picture / Logo -->
            <div v-if="selectedOrg" class="flex-shrink-0">
              <img
                v-if="org.logo"
                :src="org.logo"
                alt="Organization Logo"
                class="rounded-full w-16 h-16 object-cover"
              />
              <div
                v-else
                class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm"
              >
                No Logo
              </div>
            </div>

            <!-- Org Name -->
            <div>
              <p class="text-base font-semibold text-gray-800">
                {{ org.name }}
              </p>
              <p class="text-sm text-gray-600">{{ org.location }}</p>
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

      <!-- Organization Details Modal -->
      <dialog ref="orgModal" class="modal sm:modal-middle">
        <div class="modal-box max-w-2xl relative font-poppins">
          <!-- Close button -->
          <button
            @click="closeModal"
            class="btn btn-sm btn-circle absolute right-2 top-2"
          >
            ✕
          </button>

          <!-- Organization Info -->
          <div
            v-if="selectedOrg && selectedOrg.id"
            class="flex items-center gap-3 mb-3"
          >
            <img
              v-if="(selectedOrg && selectedOrg.logo) || (org && org.logo)"
              :src="selectedOrg?.logo || org?.logo"
              alt="Organization Logo"
              class="w-16 h-16 rounded-full object-cover"
            />

            <div
              v-else
              class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm"
            >
              No Logo
            </div>
            <div>
              <h2 class="text-xl font-bold">
                {{ selectedOrg.name || "No Name" }}
              </h2>
              <p class="text-sm text-gray-600">
                {{ selectedOrg.location || "No location available" }}
              </p>
            </div>
          </div>

          <!-- Website -->
          <p v-if="selectedOrg && selectedOrg.id" class="text-sm mb-5">
            <a
              :href="selectedOrg.website"
              target="_blank"
              class="text-blue-600 underline"
            >
              {{ selectedOrg.website }}
            </a>
          </p>

          <!-- Career Posts -->
          <div class="mt-4">
            <h3 class="text-base font-semibold mb-3">Career Posts</h3>
            <div class="flex overflow-x-auto space-x-3 pb-2 snap-x">
              <div
                v-for="career in selectedOrg.careers"
                :key="career.id"
                class="snap-start w-[180px] p-3 bg-gray-100 rounded-lg shadow-sm"
                @click="openCareerModal(career)"
              >
                <h4 class="font-semibold text-sm">{{ career.position }}</h4>
                <p class="text-[11px] text-gray-600">
                  {{ formatDateTime(career.deadlineOfSubmission) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Training Posts -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-3">Training Posts</h3>
            <div class="flex overflow-x-auto space-x-3 pb-2 snap-x">
              <div
                v-for="training in selectedOrg.trainings"
                :key="training.id"
                class="snap-start w-[180px] p-3 bg-gray-100 rounded-lg shadow-sm cursor-pointer"
                @click="openTrainingModal(training)"
              >
                <h4 class="font-semibold text-sm">{{ training.title }}</h4>
                <p class="text-[11px] text-gray-600">
                  {{ formatDateTime(training.schedule) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </dialog>

      <!-- ✅ Career Modal -->
      <div
        v-if="selectedCareer"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
      >
        <div class="modal-box relative font-poppins">
          <button
            @click="closeCareerModal"
            class="btn btn-sm btn-circle absolute right-2 top-2"
          >
            ✕
          </button>

          <div v-if="selectedCareer">
            <h2 class="text-xl font-bold mb-2">
              {{ selectedCareer.title || selectedCareer.career?.position }}
            </h2>
            <p class="text-sm text-gray-600 mb-2">
              Organization:
              {{ selectedCareer.organizationName }}
            </p>

            <!-- ✅ Bookmark and Apply Buttons -->
            <div class="mt-4 flex gap-3 justify-end">
              <button
                class="btn btn-outline btn-sm"
                @click="toggleCareerBookmark(selectedCareer)"
              >
                {{
                  isCareerBookmarked(selectedCareer.careerID)
                    ? "Bookmarked"
                    : "Bookmark"
                }}
              </button>

              <button
                class="btn btn-primary btn-sm"
                @click="applyToCareer(selectedCareer)"
                :disabled="hasApplied(selectedCareer.careerID)"
              >
                {{ hasApplied(selectedCareer.careerID) ? "Applied" : "Apply" }}
              </button>
            </div>

            <p>
              <strong>Details:</strong>
              {{
                selectedCareer.detailsAndInstructions ||
                selectedCareer.career?.detailsAndInstructions
              }}
            </p>
            <p>
              <strong>Qualifications:</strong>
              {{
                selectedCareer.qualifications ||
                selectedCareer.career?.qualifications
              }}
            </p>
            <p>
              <strong>Requirements:</strong>
              {{ selectedCareer.career?.requirements }}
            </p>
            <p>
              <strong>Application Address:</strong>
              {{
                selectedCareer.applicationLetterAddress ||
                selectedCareer.career?.applicationLetterAddress
              }}
            </p>
            <p>
              <strong>Deadline:</strong>
              {{
                formatDateTime(
                  selectedCareer.deadlineOfSubmission ||
                    selectedCareer.career?.deadlineOfSubmission
                )
              }}
            </p>

            <!-- ✅ Interview Info (only if the applicant has one) -->
            <div
              v-if="
                selectedCareer.interviewSchedule ||
                selectedCareer.interviewMode ||
                selectedCareer.interviewLink ||
                selectedCareer.interviewLocation
              "
            >
              <div class="divider"></div>
              <p v-if="selectedCareer.interviewSchedule">
                <strong>Interview Schedule:</strong>
                {{ formatDateTime(selectedCareer.interviewSchedule) }}
              </p>
              <p v-if="selectedCareer.interviewMode">
                <strong>Interview Mode:</strong>
                {{ selectedCareer.interviewMode }}
              </p>
              <p v-if="selectedCareer.interviewLink">
                <strong>Interview Link:</strong>
                <a
                  :href="selectedCareer.interviewLink"
                  target="_blank"
                  class="text-blue-500 underline"
                >
                  {{ selectedCareer.interviewLink }}
                </a>
              </p>
              <p v-if="selectedCareer.interviewLocation">
                <strong>Interview Location:</strong>
                {{ selectedCareer.interviewLocation }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Training Modal -->
      <dialog
        ref="trainingModal"
        class="modal sm:modal-middle z-50"
        v-show="selectedTraining"
      >
        <div class="modal-box max-w-3xl relative font-poppins">
          <button
            @click="closeTrainingModal"
            class="btn btn-sm btn-circle absolute right-2 top-2"
          >
            ✕
          </button>

          <div v-if="selectedTraining">
            <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
            <p class="text-sm text-gray-600 mb-2">
              Organization: {{ selectedTraining.organization?.name }}
            </p>
            <div class="mt-4 flex gap-3 justify-end">
              <!-- Bookmark -->
              <button
                @click="toggleTrainingBookmark(selectedTraining)"
                class="btn btn-outline btn-sm"
              >
                {{
                  bookmarks.has(selectedTraining.trainingID)
                    ? "Bookmarked"
                    : "Bookmark"
                }}
              </button>
              <!-- Register/Unregister -->
              <button
                @click="toggleRegistration(selectedTraining)"
                class="btn btn-sm text-white"
                :class="isRegistered ? 'bg-gray-500' : 'bg-customButton'"
              >
                {{
                  registeredTrainings.has(selectedTraining.trainingID)
                    ? "Unregister"
                    : "Register"
                }}
              </button>
            </div>
            <p><strong>Mode:</strong> {{ selectedTraining.mode }}</p>
            <p><strong>Details:</strong> {{ selectedTraining.description }}</p>

            <p v-if="selectedTraining.location">
              <strong>Location:</strong> {{ selectedTraining.location }}
            </p>

            <p v-if="selectedTraining.trainingLink">
              <strong>Link:</strong>
              <a
                :href="selectedTraining.trainingLink"
                target="_blank"
                class="text-blue-500 underline"
              >
                {{ selectedTraining.trainingLink }}
              </a>
            </p>

            <p>
              <strong>Schedule:</strong>
              {{ formatDateTime(selectedTraining.schedule) }}
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
                :value="`http://127.0.0.1:8000/attendance?trainingID=${selectedTraining.trainingID}&key=${selectedTraining.attendance_key}`"
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
        </div>
      </dialog>
    </main>
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
  </div>
</template>

<style scoped>
.modal {
  border: none;
  border-radius: 0.5rem;
  padding: 0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal::backdrop {
  background: rgba(0, 0, 0, 0.4);
}
</style>
