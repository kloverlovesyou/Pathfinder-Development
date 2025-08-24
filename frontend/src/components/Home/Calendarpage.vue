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
      <svg
        width="32"
        height="33"
        viewBox="0 0 26 27"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M2.16669 9.625C2.16669 7.73938 2.16669 6.79657 2.75247 6.21079C3.33826 5.625 4.28107 5.625 6.16669 5.625H19.8334C21.719 5.625 22.6618 5.625 23.2476 6.21079C23.8334 6.79657 23.8334 7.73938 23.8334 9.625V10.25C23.8334 10.7214 23.8334 10.9571 23.6869 11.1036C23.5405 11.25 23.3048 11.25 22.8334 11.25H3.16669C2.69528 11.25 2.45958 11.25 2.31313 11.1036C2.16669 10.9571 2.16669 10.7214 2.16669 10.25V9.625Z"
          fill="#44576D"
        />
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M2.16669 20.75C2.16669 22.6356 2.16669 23.5784 2.75247 24.1642C3.33826 24.75 4.28107 24.75 6.16669 24.75H19.8334C21.719 24.75 22.6618 24.75 23.2476 24.1642C23.8334 23.5784 23.8334 22.6356 23.8334 20.75V14.5C23.8334 14.0286 23.8334 13.7929 23.6869 13.6464C23.5405 13.5 23.3048 13.5 22.8334 13.5H3.16669C2.69528 13.5 2.45958 13.5 2.31313 13.6464C2.16669 13.7929 2.16669 14.0286 2.16669 14.5V20.75ZM7.58335 16.75C7.58335 16.2786 7.58335 16.0429 7.7298 15.8964C7.87625 15.75 8.11195 15.75 8.58335 15.75H10.9167C11.3881 15.75 11.6238 15.75 11.7702 15.8964C11.9167 16.0429 11.9167 16.2786 11.9167 16.75V17C11.9167 17.4714 11.9167 17.7071 11.7702 17.8536C11.6238 18 11.3881 18 10.9167 18H8.58335C8.11195 18 7.87625 18 7.7298 17.8536C7.58335 17.7071 7.58335 17.4714 7.58335 17V16.75ZM7.7298 20.3964C7.58335 20.5429 7.58335 20.7786 7.58335 21.25V21.5C7.58335 21.9714 7.58335 22.2071 7.7298 22.3536C7.87625 22.5 8.11195 22.5 8.58335 22.5H10.9167C11.3881 22.5 11.6238 22.5 11.7702 22.3536C11.9167 22.2071 11.9167 21.9714 11.9167 21.5V21.25C11.9167 20.7786 11.9167 20.5429 11.7702 20.3964C11.6238 20.25 11.3881 20.25 10.9167 20.25H8.58335C8.11195 20.25 7.87625 20.25 7.7298 20.3964ZM14.0834 16.75C14.0834 16.2786 14.0834 16.0429 14.2298 15.8964C14.3762 15.75 14.612 15.75 15.0834 15.75H17.4167C17.8881 15.75 18.1238 15.75 18.2702 15.8964C18.4167 16.0429 18.4167 16.2786 18.4167 16.75V17C18.4167 17.4714 18.4167 17.7071 18.2702 17.8536C18.1238 18 17.8881 18 17.4167 18H15.0834C14.612 18 14.3762 18 14.2298 17.8536C14.0834 17.7071 14.0834 17.4714 14.0834 17V16.75ZM14.2298 20.3964C14.0834 20.5429 14.0834 20.7786 14.0834 21.25V21.5C14.0834 21.9714 14.0834 22.2071 14.2298 22.3536C14.3762 22.5 14.612 22.5 15.0834 22.5H17.4167C17.8881 22.5 18.1238 22.5 18.2702 22.3536C18.4167 22.2071 18.4167 21.9714 18.4167 21.5V21.25C18.4167 20.7786 18.4167 20.5429 18.2702 20.3964C18.1238 20.25 17.8881 20.25 17.4167 20.25H15.0834C14.612 20.25 14.3762 20.25 14.2298 20.3964Z"
          fill="#44576D"
        />
        <path
          d="M7.58331 3.375L7.58331 6.75"
          stroke="#44576D"
          stroke-width="2"
          stroke-linecap="round"
        />
        <path
          d="M18.4167 3.375L18.4167 6.75"
          stroke="#44576D"
          stroke-width="2"
          stroke-linecap="round"
        />
      </svg>

      <h2 class="text-2xl font-bold">Calendar</h2>
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

    <!--List for small screen-->
    <div>
      <div class="p-4 rounded-lg lg:hidden">
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
