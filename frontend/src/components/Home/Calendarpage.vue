<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

// Use a simple data structure for schedules: id, title, date
const schedules = ref([
  { id: 1, title: "Project meeting", date: "2025-08-10" },
  { id: 2, title: "Dentist appointment", date: "2025-08-15" },
  { id: 3, title: "Team lunch", date: "2025-08-15" },
  { id: 4, title: "Weekly standup", date: "2025-08-18" },
  { id: 5, title: "Submit report", date: "2025-08-25" },
  { id: 6, title: "Vacation begins", date: "2025-09-01" },
  { id: 7, title: "Project deadline", date: "2025-07-28" },
]);

// State for managing the modal
const isModalOpen = ref(false);
const selectedSchedule = ref(null);

// State for the current month and year
const currentDate = ref(new Date());

// Function to handle clicking on a schedule
const handleScheduleClick = (schedule) => {
  selectedSchedule.value = schedule;
  isModalOpen.value = true;
};

// Function to handle deleting a schedule
const handleDeleteSchedule = (id) => {
  schedules.value = schedules.value.filter((schedule) => schedule.id !== id);
  isModalOpen.value = false;
  selectedSchedule.value = null;
};

// Function to navigate to the previous month
const handlePreviousMonth = () => {
  const newDate = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  );
  currentDate.value = newDate;
};

// Function to navigate to the next month
const handleNextMonth = () => {
  const newDate = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  );
  currentDate.value = newDate;
};

// Computed property to get the days in the currently selected month
const daysInMonth = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  return Array.from({ length: daysInMonth }, (_, i) => i + 1);
});

// Computed property for month and year display
const monthYearDisplay = computed(() => {
  return currentDate.value.toLocaleString("default", {
    month: "long",
    year: "numeric",
  });
});

// Computed properties for calendar rendering logic
const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());
const firstDayOfMonth = computed(() =>
  new Date(currentYear.value, currentMonth.value, 1).getDay()
);

const getSchedulesForDay = (day) => {
  const dateStr = `${currentYear.value}-${String(
    currentMonth.value + 1
  ).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
  return schedules.value.filter((schedule) => schedule.date === dateStr);
};
</script>

<template>
  <div class="flex flex-col min-h-screen font-poppins p-4">
    <div class="flex justify-start gap-4 mb-4">
      <button class="group" @click="router.push('/homepage')">
        <svg
          class="size-6 group-hover:hidden"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
            stroke="#6682A3"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M20.5 12H3.66998"
            stroke="#6682A3"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>

        <svg
          class="size-6 hidden group-hover:block"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
            stroke="#44576D"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M20.5 12H3.66998"
            stroke="#44576D"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </button>

      <h2 class="text-2xl font-bold text-dark-slate">Calendar</h2>
    </div>
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

    <!-- Calendar Grid -->
    <div
      class="w-full max-w-6xl p-6 bg-white rounded-2xl shadow-lg hidden lg:block"
    >
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
            :key="schedule.id"
            @click="handleScheduleClick(schedule)"
            class="cursor-pointer mt-1 w-full bg-blue-100 text-blue-800 text-sm p-1 rounded-md hover:bg-blue-200 transition-colors duration-200 truncate"
          >
            {{ schedule.title }}
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Modal -->
    <div
      v-if="isModalOpen && selectedSchedule"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50 hidden lg:block"
    >
      <div
        class="bg-white rounded-2xl p-6 shadow-xl w-full max-w-sm transform transition-all"
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

    <!--List for small screen-->
    <div>
      <div class="bg-white p-4 rounded-lg shadow-md lg:hidden">
        <div
          class="bg-blue-gray p-4 rounded-md cally bg-base-100 border border-base-300 shadow-lg rounded-box flex justify-center"
        >
          <calendar-date>
            <svg
              aria-label="Previous"
              class="fill-current size-4"
              slot="previous"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
            >
              <path fill="currentColor" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
            </svg>
            <svg
              aria-label="Next"
              class="fill-current size-4"
              slot="next"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
            >
              <path fill="currentColor" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
            </svg>
            <calendar-month></calendar-month>
          </calendar-date>
        </div>
        <div class="mt-8">
          <h2 class="text-xl font-bold mb-4">Upcoming</h2>
          <div class="space-y-4">
            <div class="p-4 bg-gray-100 rounded-lg">
              <h3 class="font-semibold hover:text-dark-slate">
                Professional development in emerging technologies and cognitive
                enhancement
              </h3>
              <p class="text-gray-600 text-sm">Tomorrow, 10:00 AM</p>
            </div>
            <div class="p-4 bg-gray-100 rounded-lg">
              <h3 class="font-semibold hover:text-dark-slate">
                Mind Over Machine: Navigating AI in Everyday Life
              </h3>
              <p class="text-gray-600 text-sm">Dec 15</p>
            </div>
            <div class="p-4 bg-gray-100 rounded-lg">
              <h3 class="font-semibold hover:text-dark-slate">
                Business Conference
              </h3>
              <p class="text-gray-600 text-sm">Dec 18, 2:00 PM</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
