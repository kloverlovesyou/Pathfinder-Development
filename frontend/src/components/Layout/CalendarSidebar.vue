<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

// --- Career events for applications the user applied for ---

async function loadApplicationEvents(applicantID, token) {
  try {
    const { data: apps } = await axios.get(`${API_BASE_URL}/applications`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    console.log("📋 Applications fetched:", apps?.length || 0);
    console.log("📋 Sample application data:", apps?.[0]);

    // Filter applications with interviewSchedule
    const appsWithInterview = (apps || []).filter(
      (app) => {
        const hasSchedule = app.interviewSchedule && app.interviewSchedule !== null && app.interviewSchedule !== '';
        if (hasSchedule) {
          console.log("✅ Application has interviewSchedule:", {
            applicationID: app.applicationID,
            interviewSchedule: app.interviewSchedule,
            title: app.title,
            applicantID: app.applicantID,
            targetApplicantID: applicantID,
          });
        }
        return hasSchedule;
      }
    );

    console.log("📅 Applications with interview schedule:", appsWithInterview.length);

    // Backend already filters by applicantID, so all apps returned are for this user
    // Deduplicate applications by applicationID first
    const uniqueApps = appsWithInterview.filter((app, index, self) => 
      index === self.findIndex(a => a.applicationID === app.applicationID)
    );
    
    console.log("🔍 Unique applications (after deduplication):", uniqueApps.length, "out of", appsWithInterview.length);
    
    // Process all applications with interviewSchedule
    const interviewEvents = uniqueApps
      .map((app) => {
        console.log("🔍 Processing application:", {
          applicationID: app.applicationID,
          interviewSchedule: app.interviewSchedule,
          title: app.title,
          organizationName: app.organizationName,
        });

        const isoDate = toISODate(app.interviewSchedule);
        console.log("📅 Parsed date:", {
          original: app.interviewSchedule,
          parsed: isoDate,
          applicationID: app.applicationID,
        });
        
        if (!isoDate) {
          console.warn("⚠️ Could not parse interviewSchedule:", app.interviewSchedule);
          return null;
        }

        const event = {
          type: "career",
          date: isoDate,
          applicationID: app.applicationID,
          careerID: app.careerID,
          title: app.title || "Career Interview",
          organization: app.organizationName || "Unknown Organization",
          interviewSchedule: app.interviewSchedule,
          interviewMode: app.interviewMode,
          interviewLink: app.interviewLink,
          interviewLocation: app.interviewLocation,
        };

        console.log("✅ Created interview event:", {
          date: event.date,
          title: event.title,
          type: event.type,
          fullEvent: event,
        });
        return event;
      })
      .filter(Boolean);

    console.log("📅 Total interview events created:", interviewEvents.length);

    interviewEvents.forEach((event) => {
      if (!events.value[event.date]) {
        events.value[event.date] = [];
        console.log(`📆 Created new date entry: ${event.date}`);
      }
      
      // Check if this event already exists (by applicationID for career events, or by other unique identifier)
      const existingEvent = events.value[event.date].find((e) => {
        if (event.type === 'career' && e.type === 'career') {
          return e.applicationID === event.applicationID;
        }
        // For training events, you might want to check by registrationID or trainingID
        return false;
      });
      
      if (!existingEvent) {
        events.value[event.date].push(event);
        console.log(`📌 Added event to date ${event.date}:`, {
          title: event.title,
          type: event.type,
          totalEventsForDate: events.value[event.date].length,
        });
      } else {
        console.log(`⚠️ Event already exists for date ${event.date}:`, {
          title: event.title,
          type: event.type,
          applicationID: event.applicationID,
        });
      }
    });

    console.log("✅ Career interview events loaded:", interviewEvents.length);
    console.log("📊 All events by date:", Object.keys(events.value).map(date => ({
      date,
      events: events.value[date].map(e => ({ type: e.type, title: e.title }))
    })));
    console.log("📊 Career events by date:", Object.keys(events.value).filter(date => events.value[date].some(e => e.type === 'career')));
  } catch (error) {
    console.error("❌ Error loading career interview events:", error);
    console.error("❌ Error details:", error.response?.data || error.message);
  }
}

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
const props = defineProps({
  isOpen: { type: Boolean, required: true },
});
const emit = defineEmits(["open", "close", "eventClick"]);

const selectedTraining = ref(null);
const selectedPost = ref(null);

const activeSchedules = computed(() => {
  const training = selectedTraining.value;
  if (!training) return [];

  const schedulesArray =
    Array.isArray(training.schedules) && training.schedules.length
      ? training.schedules
      : [];

  if (schedulesArray.length) return schedulesArray;

  const fallbackSchedule =
    training.schedule || training.start_time || training.scheduleDate;

  if (fallbackSchedule) {
    return [
      {
        schedule: fallbackSchedule,
        end_time: training.end_time,
        mode: training.mode,
        location: training.location,
        trainingLink: training.trainingLink,
      },
    ];
  }

  return [];
});

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

function formatScheduleFull(datetime) {
  if (!datetime) return "No schedule set";

  try {
    const [datePart, timePart] = datetime.split(/[ T]/);
    const [year, month, day] = datePart.split("-");
    const [hour = "00", minute = "00"] = (timePart || "00:00:00").split(":");

    const parsed = new Date(
      Number(year),
      Number(month) - 1,
      Number(day),
      Number(hour),
      Number(minute)
    );

    return parsed.toLocaleString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  } catch (error) {
    return datetime;
  }
}

function formatScheduleTime(datetime) {
  if (!datetime) return "";

  try {
    const [datePart, timePart] = datetime.split(/[ T]/);
    const [year, month, day] = datePart.split("-");
    const [hour = "00", minute = "00"] = (timePart || "00:00:00").split(":");

    const parsed = new Date(
      Number(year),
      Number(month) - 1,
      Number(day),
      Number(hour),
      Number(minute)
    );

    return parsed.toLocaleString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  } catch (error) {
    return "";
  }
}

// --- CALENDAR LOGIC ---
const selectedDate = ref("");
const displayDate = computed(() => {
  if (!selectedDate.value) return "";
  const [year, month, day] = selectedDate.value.split("-").map(Number);
  if ([year, month, day].some((n) => Number.isNaN(n))) return selectedDate.value;
  const date = new Date(year, month - 1, day);
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
});
const events = ref({}); // Map of date → [events]
const dayEvents = ref([]);

// --- BUILT-IN CALENDAR LOGIC ---
const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const currentMonthName = computed(() => {
  return new Date(currentYear.value, currentMonth.value).toLocaleString("default", {
    month: "long",
    year: "numeric",
  });
});

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);

  const days = [];

  // Fill empty slots before first day
  for (let i = 0; i < firstDay.getDay(); i++) {
    days.push(null);
  }

  // Add all days of the month
  for (let d = 1; d <= lastDay.getDate(); d++) {
    days.push(new Date(currentYear.value, currentMonth.value, d));
  }

  return days;
});

const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const isToday = (date) => {
  if (!date) return false;
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
};

const isSelected = (date) => {
  if (!date || !selectedDate.value) return false;
  const dateStr = toISODate(date);
  return dateStr === selectedDate.value;
};

const hasEvents = (date) => {
  if (!date) return false;
  const dateStr = toISODate(date);
  return events.value[dateStr] && events.value[dateStr].length > 0;
};

const selectDate = (date) => {
  if (!date) return;
  const dateStr = toISODate(date);
  showEvents(dateStr);
};

// --- Initialize calendar: fetch events & select today ---
onMounted(async () => {
  await loadUserEvents();
});
// --- EVENTS LOGIC ---
async function loadRegistrationEvents(applicantID, token) {
  const response = await axios.get(`${API_BASE_URL}/registrations`, {
    headers: { Authorization: `Bearer ${token}` },
  });

  const registrations = (response.data || []).filter(
    (reg) => Number(reg.applicantID) === Number(applicantID)
  );

  registrations.forEach((reg) => {
    const training = reg.training || {};
    const normalizedTrainingID = Number(
      reg.trainingID ??
        reg.training_id ??
        training.trainingID ??
        training.training_id ??
        reg.TrainingID ??
        0
    );
    const schedules =
      Array.isArray(training.schedules) && training.schedules.length
        ? training.schedules
        : [
            {
              schedule:
                reg.schedule ||
                reg.start_time ||
                training.schedule ||
                training.start_time,
              end_time: reg.end_time || training.end_time,
              mode: reg.mode || training.mode,
              location: reg.location || training.location,
              trainingLink: reg.trainingLink || training.trainingLink,
            },
          ];

    const schedulesByDate = schedules.reduce((acc, schedule) => {
      const isoDate = toISODate(schedule.schedule || schedule.start_time);
      if (!isoDate) return acc;
      if (!acc[isoDate]) acc[isoDate] = [];
      acc[isoDate].push(schedule);
      return acc;
    }, {});

    Object.entries(schedulesByDate).forEach(([isoDate, groupedSchedules]) => {
      if (!events.value[isoDate]) events.value[isoDate] = [];

      const alreadyListed = events.value[isoDate].some(
        (existing) =>
          existing.type === "training" &&
          existing.registrationID === reg.registrationID
      );

      if (alreadyListed) return;

      const primarySchedule = groupedSchedules[0] || {};

      events.value[isoDate].push({
        type: "training",
        date: isoDate,
        trainingID: normalizedTrainingID,
        registrationID: reg.registrationID,
        title: training.title || "Training",
        organization:
          training.organization?.name ||
          reg.organizationName ||
          "Unknown Organization",
        description: training.description || reg.description || "",
        mode: primarySchedule.mode || training.mode || reg.mode,
        location:
          primarySchedule.location || training.location || reg.location,
        trainingLink:
          primarySchedule.trainingLink ||
          training.trainingLink ||
          reg.trainingLink,
        schedule: primarySchedule.schedule || primarySchedule.start_time,
        start_time: primarySchedule.schedule || primarySchedule.start_time,
        end_time: primarySchedule.end_time,
        schedules,
      });
    });
  });
}

async function loadUserEvents() {
  const token = localStorage.getItem("token");
  const savedUser = localStorage.getItem("user");

  if (!token || !savedUser) {
    console.warn("⚠️ Missing user or token in localStorage");
    return;
  }

  const user = JSON.parse(savedUser);
  if (!user.applicantID) {
    console.warn("⚠️ User has no applicantID");
    return;
  }

  events.value = {};

  try {
    await Promise.all([
      loadRegistrationEvents(user.applicantID, token),
      loadApplicationEvents(user.applicantID, token),
    ]);
  } catch (error) {
    console.error("❌ Error loading events:", error);
  }

  const currentDate = selectedDate.value || toISODate(new Date());
  showEvents(currentDate);
  
  // Update calendar view to show selected date's month
  if (currentDate) {
    const [year, month] = currentDate.split("-").map(Number);
    if (year && month) {
      currentYear.value = year;
      currentMonth.value = month - 1;
    }
  }
}

function showEvents(date) {
  selectedDate.value = date;
  dayEvents.value = events.value[date] || [];
  console.log("📅 Events for", date, ":", {
    totalEvents: dayEvents.value.length,
    events: dayEvents.value.map(e => ({ type: e.type, title: e.title })),
    allEventsForDate: events.value[date],
  });
}

function handleDateChange(event) {
  const value = event?.target?.value;
  if (!value) return;
  showEvents(value);
  // Update calendar view to show selected month
  const [year, month, day] = value.split("-").map(Number);
  currentYear.value = year;
  currentMonth.value = month - 1;
}

watch(
  () => props.isOpen,
  async (isOpen) => {
    if (isOpen) {
      await loadUserEvents();
    }
  }
);

// --- INITIALIZE CALENDAR ---
onMounted(async () => {
  await nextTick();
  await loadUserEvents();
});

// Format for comparisons / mapping
function toISODate(d) {
  if (!d) return "";
  
  // If it's already a string in YYYY-MM-DD format, return it
  if (typeof d === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(d)) {
    return d;
  }
  
  // Handle datetime strings (ISO format or Y-m-d H:i:s format)
  if (typeof d === 'string') {
    // Extract date part from ISO format (2025-12-01T02:55:00.000000Z) or space-separated (2025-12-01 02:55:00)
    const dateMatch = d.match(/^(\d{4}-\d{2}-\d{2})/);
    if (dateMatch) {
      return dateMatch[1]; // Return YYYY-MM-DD
    }
  }
  
  // If it's a Date object, format it directly to avoid timezone issues
  const date = d instanceof Date ? d : new Date(d);
  if (isNaN(date.getTime())) {
    console.warn("⚠️ Invalid date value:", d);
    return "";
  }
  
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  
  return `${year}-${month}-${day}`;
}

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
        { '!bg-white': true },
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
        <!-- Built-in Calendar -->
        <div class="w-full flex flex-col gap-2">
          <label class="text-sm font-medium text-gray-600 mb-2">
            Select a date
          </label>
          
          <!-- Calendar Header -->
          <div class="flex items-center justify-between mb-2">
            <button
              @click="prevMonth"
              class="p-1 hover:bg-gray-100 rounded transition-colors"
              aria-label="Previous month"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-gray-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
            </button>
            <h3 class="text-sm font-semibold text-gray-700">
              {{ currentMonthName }}
            </h3>
            <button
              @click="nextMonth"
              class="p-1 hover:bg-gray-100 rounded transition-colors"
              aria-label="Next month"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-gray-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </div>

          <!-- Calendar Grid -->
          <div class="bg-white border border-gray-200 rounded-lg p-2" style="background: #fff !important;">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-1 mb-1">
              <div
                v-for="dayName in dayNames"
                :key="dayName"
                class="text-xs font-medium text-gray-500 text-center py-1"
              >
                {{ dayName }}
              </div>
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-1">
              <div
                v-for="(date, index) in calendarDays"
                :key="index"
                class="aspect-square flex items-center justify-center"
              >
                <button
                  v-if="date"
                  @click="selectDate(date)"
                  :class="[
                    'w-full h-full rounded-md text-sm font-medium transition-all',
                    isSelected(date)
                      ? 'bg-blue-600 text-white shadow-md'
                      : isToday(date)
                      ? 'bg-blue-100 text-blue-700 font-bold'
                      : hasEvents(date)
                      ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      : 'text-gray-600 hover:bg-gray-50',
                  ]"
                >
                  <div class="flex flex-col items-center justify-center h-full">
                    <span>{{ date.getDate() }}</span>
                    <span
                      v-if="hasEvents(date)"
                      :class="[
                        'w-1.5 h-1.5 rounded-full mt-0.5',
                        isSelected(date) ? 'bg-white' : 'bg-blue-500',
                      ]"
                    ></span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Events Panel -->
        <div class="bg-white w-full max-w-[250px] h-72 overflow-y-auto" style="background: #fff !important;">
          <h1 class="text-lg font-semibold mb-1">Upcoming Events</h1>
          <h2 class="mb-2 text-sm text-gray-600">
            on
            <span class="font-medium">
              {{ displayDate || "Select a date" }}
            </span>
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
              <div class="flex-1">
                <h3 class="font-semibold text-sm">
                  {{
                    event.title ??
                    (event.type === "career"
                      ? "Career Interview"
                      : "Training Event")
                  }}
                </h3>

                <!-- Show organization for career events -->
                <p
                  v-if="event.type === 'career' && event.organization"
                  class="text-xs text-gray-500 mt-0.5"
                >
                  {{ event.organization }}
                </p>

                <!-- Show organization for training events -->
                <p
                  v-if="event.type === 'training' && event.organization"
                  class="text-xs text-gray-500 mt-0.5"
                >
                  {{ event.organization }}
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
    <div
      v-if="selectedTraining"
      class="modal-overlay"
      @click.self="closeModal"
    >
      <div class="training-details-modal font-poppins">
        <button class="modal-close-btn" @click="closeModal">✕</button>

        <template v-if="selectedTraining">
          <h3 class="modal-title">
            {{ selectedTraining.title || "Untitled Training" }}
          </h3>

          <p class="training-info">
            <strong>Organization:</strong>
            {{
              selectedTraining.organization?.name ||
              selectedTraining.organization ||
              selectedTraining.organizationName ||
              selectedTraining.training?.organization?.name ||
              "Unknown"
            }}
          </p>

          <p v-if="selectedTraining.description" class="training-info">
            <strong>Description:</strong> {{ selectedTraining.description }}
          </p>

          <div class="training-info">
            <strong>Schedule/s:</strong>
          </div>

          <div class="schedules-container">
            <div v-if="activeSchedules.length" class="schedules-grid">
              <div
                v-for="(schedule, index) in activeSchedules"
                :key="schedule.trainingScheduleID || index"
                class="schedule-card"
              >
                <div class="schedule-date-time">
                  <svg
                    class="schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span>
                    {{
                      formatScheduleFull(schedule.schedule || schedule.start_time)
                    }}
                    <template v-if="schedule.end_time">
                      - {{ formatScheduleTime(schedule.end_time) }}
                    </template>
                  </span>
                </div>

                <div
                  class="schedule-mode-badge"
                  :class="
                    (schedule.mode || '').toLowerCase() === 'on-site'
                      ? 'mode-onsite'
                      : 'mode-online'
                  "
                >
                  <svg
                    class="mode-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span>{{ schedule.mode || "Mode not set" }}</span>
                </div>

                <div
                  v-if="
                    (schedule.mode || '').toLowerCase() === 'on-site' &&
                    schedule.location
                  "
                  class="schedule-location"
                >
                  <svg
                    class="schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span>{{ schedule.location }}</span>
                </div>

                <div
                  v-else-if="
                    (schedule.mode || '').toLowerCase() === 'online' &&
                    schedule.trainingLink
                  "
                  class="schedule-link"
                >
                  <svg
                    class="schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                    />
                  </svg>
                  <a
                    :href="schedule.trainingLink"
                    target="_blank"
                    class="training-link"
                  >
                    {{ schedule.trainingLink }}
                  </a>
                </div>
              </div>
            </div>

            <div v-else class="schedule-card">
              <p class="text-gray-500">No schedule set</p>
            </div>
          </div>
        </template>
      </div>
    </div>

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
      <div class="modal-box relative font-poppins" style="background: #fff !important;">
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

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.training-details-modal {
  background: #fff !important;
  padding: 2rem;
  border-radius: 1rem;
  width: min(95vw, 900px);
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #4a4a4a;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.training-info {
  margin: 0.4rem 0;
  color: #333;
}

.schedules-container {
  margin: 0.75rem 0;
}

.schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.schedule-card {
  background: #f5f5f5;
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  border: 1px solid transparent;
  transition: all 0.3s ease;
}

.schedule-card:hover {
  background: #e8e8e8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
}

.schedule-date-time {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #333;
  font-size: 0.85rem;
}

.schedule-icon {
  width: 16px;
  height: 16px;
  color: #60a5fa;
  flex-shrink: 0;
}

.schedule-mode-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.35rem 0.6rem;
  border-radius: 6px;
  border: 2px solid;
  width: fit-content;
  font-size: 0.8rem;
  font-weight: 500;
}

.mode-onsite {
  background-color: #dbeafe;
  border-color: #3b82f6;
  color: #1e40af;
}

.mode-online {
  background-color: #fef3c7;
  border-color: #f59e0b;
  color: #92400e;
}

.mode-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.schedule-location,
.schedule-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #333;
  font-size: 0.85rem;
}

.training-link {
  color: #3b82f6;
  text-decoration: underline;
  word-break: break-all;
}

/* Force white backgrounds for modals, inputs, text boxes, and containers regardless of dark mode */
.modal-box {
  background: #fff !important;
}

aside.bg-white {
  background: #fff !important;
}

input,
textarea {
  background: #fff !important;
  color: #000 !important;
}

input::placeholder,
textarea::placeholder {
  color: #9ca3af !important;
}

/* Ensure all white background containers stay white in dark mode */
.bg-white {
  background-color: #fff !important;
}
</style>
