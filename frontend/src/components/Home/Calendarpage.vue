<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";

const calendarRef = ref(null);
const posts = ref([]); // will hold trainings from backend
const eventsByDate = ref({});
const selectedEvents = ref([]);
const selectedDate = ref("");

// ✅ Fetch trainings from backend
async function fetchScheduledTrainings() {
  try {
    const token = localStorage.getItem("token"); // Adjust if you store token differently

    const res = await axios.get("http://127.0.0.1:8000/api/trainings", {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Map response to match your structure
    posts.value = res.data.map((training) => ({
      trainingID: training.trainingID,
      title: training.title,
      schedule: training.schedule,
      organizationID: training.organizationID,
      location: training.location,
      trainingLink: training.trainingLink,
      mode: training.mode,
      description: training.description,
    }));

    buildEvents();
    highlightEventDays();

    console.log("✅ Trainings fetched:", posts.value);
  } catch (error) {
    console.error("❌ Error fetching trainings:", error);
  }
}

// ✅ Build a map of events per date
function buildEvents() {
  eventsByDate.value = {};

  posts.value.forEach((training) => {
    const dateKey = new Date(training.schedule).toISOString().split("T")[0];
    if (!eventsByDate.value[dateKey]) eventsByDate.value[dateKey] = [];
    eventsByDate.value[dateKey].push(training);
  });
}

// ✅ Highlight calendar days that have events
function highlightEventDays() {
  const calendar = calendarRef.value;
  if (!calendar) return;

  calendar.querySelectorAll("[data-date]").forEach((el) => {
    const date = el.getAttribute("data-date");
    el.classList.remove("highlight-day");
    if (eventsByDate.value[date]) {
      el.classList.add("highlight-day");
    }
  });
}

// ✅ Show events for a selected date
function showEvents(date) {
  selectedDate.value = date;
  selectedEvents.value = eventsByDate.value[date] || [];
}

// ✅ On mounted
onMounted(async () => {
  await nextTick();
  await fetchScheduledTrainings();

  const calendar = calendarRef.value;
  if (!calendar) return;

  const today = new Date().toISOString().split("T")[0];

  // Listen to calendar changes
  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);

    // Highlight selected day
    calendar.querySelectorAll("[data-date]").forEach((el) =>
      el.classList.remove("selected-day")
    );
    const selectedEl = calendar.querySelector(`[data-date="${pickedDate}"]`);
    if (selectedEl) selectedEl.classList.add("selected-day");
  });

  // Default: show today's events
  calendar.value = today;
  showEvents(today);
});
</script>

<template>
  <div class="flex flex-col min-h-screen font-poppins p-4">
    <!-- App Header with navigation -->
    <div class="mb-8 text-center hidden lg:block">
      <div class="flex items-center justify-center mt-2">
        <button
          @click="handlePreviousMonth"
          class="p-2 text-black hover:text-gray-900 transition-colors duration-200"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            class="w-6 h-6"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5L8.25 12l7.5-7.5"
            />
          </svg>
        </button>
        <p class="text-xl text-black font-semibold mx-4 w-40">
          {{ monthYearDisplay }}
        </p>
        <button
          @click="handleNextMonth"
          class="p-2 text-black hover:text-gray-900 transition-colors duration-200"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            class="w-6 h-6"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M8.25 4.5l7.5 7.5-7.5 7.5"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Calendar Grid (Desktop) -->
    <div class="w-full max-w-6xl p-6 rounded-2xl hidden lg:block">
      <div
        class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-gray-500 font-semibold mb-4"
      >
        <div
          v-for="dayName in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
          :key="dayName"
          class="p-2"
        >
          {{ dayName }}
        </div>
      </div>
      <div class="grid grid-cols-7 gap-2">
        <!-- Fill in blank spaces for the first week -->
        <div v-for="n in firstDayOfMonth" :key="`blank-${n}`" class="p-2"></div>

        <!-- Render calendar days -->
        <div
          v-for="day in daysInMonth"
          :key="day"
          class="bg-gray-50 h-32 md:h-40 rounded-lg p-2 flex flex-col items-start overflow-auto shadow-sm hover:bg-gray-100 transition-colors duration-200"
        >
          <div class="text-lg font-bold text-gray-800">{{ day }}</div>

          <div
            v-for="schedule in getSchedulesForDay(day)"
            :key="schedule.trainingID"
            @click="handleScheduleClick(schedule)"
            class="cursor-pointer mt-1 w-full bg-blue-100 text-blue-800 text-sm p-1 rounded-md hover:bg-blue-200 transition-colors duration-200 truncate"
          >
            {{ schedule.title }}
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Modal (Desktop) -->
    <div
      v-if="isModalOpen && selectedSchedule"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50 hidden lg:block"
    >
      <div
        class="rounded-2xl p-6 shadow-xl w-full max-w-sm transform transition-all"
      >
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Schedule Details</h3>
        <p class="text-lg text-gray-600 mb-2">
          <span class="font-semibold">Event:</span> {{ selectedSchedule.title }}
        </p>
        <p class="text-lg text-gray-600 mb-4">
          <span class="font-semibold">Date:</span> {{ selectedSchedule.date }}
        </p>
        <div class="flex gap-4">
          <button
            @click="handleDeleteSchedule(selectedSchedule.id)"
            class="flex-grow p-3 bg-dark-slate text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition-colors duration-200"
          >
            Delete
          </button>
          <button
            @click="isModalOpen = false"
            class="flex-grow p-3 bg-gray-300 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-400 transition-colors duration-200"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- 📱 List for small screen -->
    <div>
      <div class="p-4 rounded-lg lg:hidden">
        <div
          class="bg-blue-gray p-4 rounded-md cally bg-base-100 border border-base-300 shadow-lg rounded-box flex justify-center"
        >
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
                >
                  {{ label }}
                </div>
              </template>
            </calendar-month>
          </calendar-date>
        </div>

        <!-- ✅ Dynamic event list -->
        <div class="mt-8">
          <h2 class="text-xl font-bold mb-4">Upcoming Trainings</h2>

          <div v-if="selectedEvents.length === 0" class="text-gray-500">
            No events scheduled for {{ selectedDate || "this day" }}.
          </div>

          <div
            v-for="event in selectedEvents"
            :key="event.trainingID"
            class="p-4 bg-gray-100 rounded-lg mb-3"
          >
            <h3 class="font-semibold hover:text-dark-slate">
              {{ event.title }}
            </h3>
            <p class="text-gray-600 text-sm">
              📅 {{ new Date(event.schedule).toLocaleString() }}
            </p>
            <p class="text-gray-600 text-sm">📍 {{ event.location }}</p>
            <p class="text-gray-600 text-sm">
              🔗
              <a
                :href="event.trainingLink"
                target="_blank"
                class="text-blue-500 underline"
                >Open Link</a
              >
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>