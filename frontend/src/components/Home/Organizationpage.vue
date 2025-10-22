<script setup>
import { ref, onMounted } from "vue";

//onMounted(async () => {
// try {
//  const res = await axios.get("http://127.0.0.1:8000/api/organization");
// organizations.value = res.data;
//} catch (error) {
//console.error("Error fetching organizations:", error);
//}
//});

const isModalOpen = ref(false);
const selectedOrg = ref(null);
const isSidebarOpen = ref(false);

const organizations = ref([
  {
    id: 1,
    name: "TechNova Solutions",
    location: "Quezon City, Philippines",
    website: "https://www.technova.com",
    logo: "https://cdn-icons-png.flaticon.com/512/5968/5968705.png",
    careers: [
      { id: 1, position: "Frontend Developer", deadline: "Nov 30, 2025" },
      { id: 2, position: "UI/UX Designer", deadline: "Dec 15, 2025" },
      { id: 3, position: "UI/UX Designer", deadline: "Dec 15, 2025" },
      { id: 4, position: "UI/UX Designer", deadline: "Dec 15, 2025" },
      { id: 5, position: "UI/UX Designer", deadline: "Dec 15, 2025" },
    ],
    trainings: [
      { id: 1, title: "Vue.js Fundamentals", schedule: "Oct 25, 2025" },
      { id: 2, title: "Advanced JavaScript", schedule: "Nov 5, 2025" },
    ],
  },
  {
    id: 2,
    name: "GreenFields Agritech",
    location: "Cebu City, Philippines",
    website: "https://www.greenfields.com",
    logo: "https://cdn-icons-png.flaticon.com/512/616/616408.png",
    careers: [
      { id: 1, position: "Agricultural Analyst", deadline: "Nov 10, 2025" },
      {
        id: 2,
        position: "Field Operations Supervisor",
        deadline: "Nov 25, 2025",
      },
    ],
    trainings: [
      { id: 1, title: "Sustainable Farming 101", schedule: "Oct 28, 2025" },
      { id: 2, title: "Farm Data Analytics", schedule: "Nov 18, 2025" },
    ],
  },
  {
    id: 3,
    name: "FinEdge Bank",
    location: "Makati City, Philippines",
    website: "https://www.finedgebank.com",
    logo: "https://cdn-icons-png.flaticon.com/512/888/888879.png",
    careers: [
      { id: 1, position: "Financial Advisor", deadline: "Dec 20, 2025" },
    ],
    trainings: [
      { id: 1, title: "Financial Literacy Workshop", schedule: "Nov 12, 2025" },
      { id: 2, title: "Excel for Accountants", schedule: "Dec 3, 2025" },
    ],
  },
]);

// 🔹 Modal functions
function openModal(org) {
  selectedOrg.value = org;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedOrg.value = null;
}

// Calendar + events
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

// Modal
const selectedPost = ref(null);

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
</script>

<template>
  <main class="font-poppins">
    <div class="bg-white m-3 p-4 rounded-lg">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
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
          <div class="flex-shrink-0">
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

        <!-- Events -->
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
          d="M20 9.84003H4C3.45 9.84003 3 10.29 3 10.84V17C3 20 4.5 22 8 22H16C19.5 22 21 20 21 17V10.84C21 10.29 20.55 9.84003 20 9.84003ZM9.21 18.21C9.16 18.25 9.11 18.3 9.06 18.33C9 18.37 8.94 18.4 8.88 18.42C8.82 18.45 8.76 18.47 8.7 18.48C8.63 18.49 8.57 18.5 8.5 18.5C8.37 18.5 8.24 18.47 8.12 18.42C7.99 18.37 7.89 18.3 7.79 18.21C7.61 18.02 7.5 17.76 7.5 17.5C7.5 17.24 7.61 16.98 7.79 16.79C7.89 16.7 7.99 16.63 8.12 16.58C8.3 16.5 8.5 16.48 8.7 16.52C8.76 16.53 8.82 16.55 8.88 16.58C8.94 16.6 9 16.63 9.06 16.67C9.11 16.71 9.16 16.75 9.21 16.79C9.39 16.98 9.5 17.24 9.5 17.5C9.5 17.76 9.39 18.02 9.21 18.21ZM9.21 14.71C9.02 14.89 8.76 15 8.5 15C8.24 15 7.98 14.89 7.79 14.71C7.61 14.52 7.5 14.26 7.5 14C7.5 13.74 7.61 13.48 7.79 13.29C8.07 13.01 8.51 12.92 8.88 13.08C9.01 13.13 9.12 13.2 9.21 13.29C9.39 13.48 9.5 13.74 9.5 14C9.5 14.26 9.39 14.52 9.21 14.71ZM12.71 18.21C12.52 18.39 12.26 18.5 12 18.5C11.74 18.5 11.48 18.39 11.29 18.21C11.11 18.02 11 17.76 11 17.5C11 17.24 11.11 16.98 11.29 16.79C11.66 16.42 12.34 16.42 12.71 16.79C12.89 16.98 13 17.24 13 17.5C13 17.76 12.89 18.02 12.71 18.21ZM12.71 14.71C12.66 14.75 12.61 14.79 12.56 14.83C12.5 14.87 12.44 14.9 12.38 14.92C12.32 14.95 12.26 14.97 12.2 14.98C12.13 14.99 12.07 15 12 15C11.74 15 11.48 14.89 11.29 14.71C11.11 14.52 11 14.26 11 14C11 13.74 11.11 13.48 11.29 13.29C11.38 13.2 11.49 13.13 11.62 13.08C11.99 12.92 12.43 13.01 12.71 13.29C12.89 13.48 13 13.74 13 14C13 14.26 12.89 14.52 12.71 14.71ZM16.21 18.21C16.02 18.39 15.76 18.5 15.5 18.5C15.24 18.5 14.98 18.39 14.79 18.21C14.61 18.02 14.5 17.76 14.5 17.5C14.5 17.24 14.61 16.98 14.79 16.79C15.16 16.42 15.84 16.42 16.21 16.79C16.39 16.98 16.5 17.24 16.5 17.5C16.5 17.76 16.39 18.02 16.21 18.21ZM16.21 14.71C16.16 14.75 16.11 14.79 16.06 14.83C16 14.87 15.94 14.9 15.88 14.92C15.82 14.95 15.76 14.97 15.7 14.98C15.63 14.99 15.56 15 15.5 15C15.24 15 14.98 14.89 14.79 14.71C14.61 14.52 14.5 14.26 14.5 14C14.5 13.74 14.61 13.48 14.79 13.29C14.89 13.2 14.99 13.13 15.12 13.08C15.3 13 15.5 12.98 15.7 13.02C15.76 13.03 15.82 13.05 15.88 13.08C15.94 13.1 16 13.13 16.06 13.17C16.11 13.21 16.16 13.25 16.21 13.29C16.39 13.48 16.5 13.74 16.5 14C16.5 14.26 16.39 14.52 16.21 14.71Z"
          fill="white"
        />
      </svg>
    </button>

    <!-- Organization Details Modal -->
    <dialog v-if="isModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-2xl relative font-poppins">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>

        <!-- Organization Info -->
        <div class="flex items-center gap-3 mb-3">
          <img
            v-if="selectedOrg.logo"
            :src="selectedOrg.logo"
            alt="Organization Logo"
            class="w-14 h-14 rounded-full object-cover"
          />
          <div>
            <h2 class="text-xl font-bold">{{ selectedOrg.name }}</h2>
            <p class="text-sm text-gray-600">{{ selectedOrg.location }}</p>
          </div>
        </div>

        <!-- Website -->
        <p class="text-sm mb-5">
          <a
            :href="selectedOrg.website"
            target="_blank"
            class="text-blue-600 underline break-all"
          >
            {{ selectedOrg.website }}
          </a>
        </p>
        <!-- ✅ Career Posts -->
        <div class="mt-4">
          <h3 class="text-base font-semibold mb-3">Career Posts</h3>
          <div
            class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
            style="scrollbar-width: thin"
          >
            <div
              v-for="career in selectedOrg.careers"
              :key="career.id"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ career.position }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Deadline: {{ career.deadline }}
              </p>
            </div>
          </div>
        </div>

        <!-- ✅ Training Posts -->
        <div class="mt-6">
          <h3 class="text-base font-semibold mb-3">Training Posts</h3>
          <div
            class="flex overflow-x-auto space-x-3 pb-2 snap-x snap-mandatory"
            style="scrollbar-width: thin"
          >
            <div
              v-for="training in selectedOrg.trainings"
              :key="training.id"
              class="snap-start w-[180px] flex-shrink-0 p-3 bg-blue-gray rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-sm"
            >
              <h4 class="font-semibold text-sm leading-snug mb-1">
                {{ training.title }}
              </h4>
              <p class="text-[11px] text-gray-600 truncate">
                Schedule: {{ training.schedule }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </dialog>
  </main>
</template>
