<script setup>
import { ref, onMounted, reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import QrcodeVue from "qrcode.vue";

const router = useRouter();

const userName = ref("Guest");
const activities = ref([]);
const upcomingCount = ref(0);
const completedCount = ref(0);
const showModal = ref(false);
const selectedActivity = ref(null);

// ✅ For QR countdown timers
const qrCountdowns = reactive({});
let qrCountdownInterval = null;

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

// ✅ Logout
function logout() {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Login" });
}

// ✅ Fetch all activities for this user
async function fetchMyActivities() {
  const savedUser = localStorage.getItem("user");
  if (!savedUser) return;

  const user = JSON.parse(savedUser);
  userName.value =
    `${user.firstName || ""} ${user.lastName || ""}`.trim() || "Guest";

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +`/my-activities/${user.applicantID}`);
    activities.value = res.data.activities || [];

    console.log("Raw activities data:", activities.value);
    // ✅ Count by status
    upcomingCount.value = activities.value.filter((a) =>
      ["upcoming", "registered"].includes(a.status?.toLowerCase())
    ).length;

    completedCount.value = activities.value.filter(
      (a) => a.status?.toLowerCase() === "completed"
    ).length;

    // ✅ Start QR countdown timers
    startAllQRCountdowns();

    console.log("Activities fetched:", activities.value);
  } catch (error) {
    console.error("Error fetching activities:", error);
  }
}

// ✅ Countdown logic for QR expiration
const startAllQRCountdowns = () => {
  if (qrCountdownInterval) clearInterval(qrCountdownInterval);

  qrCountdownInterval = setInterval(() => {
    activities.value.forEach((activity) => {
      if (activity.end_time) {
        const now = new Date().getTime();
        const expiry = new Date(activity.end_time).getTime();
        const diff = expiry - now;

        if (diff > 0) {
          const mins = Math.floor(diff / 1000 / 60);
          const secs = Math.floor((diff / 1000) % 60);
          qrCountdowns[activity.id] = `${mins}m ${secs}s`;
        } else {
          qrCountdowns[activity.id] = "Expired";
        }
      }
    });
  }, 1000);
};

// ✅ Determine if training has passed
function isTrainingPassed(activity) {
  if (!activity.schedule) return false;
  const now = new Date();
  const scheduleDate = new Date(activity.schedule);
  return now >= scheduleDate;
}

function parseDate(dateStr) {
  if (!dateStr) return null;
  return new Date(dateStr.replace(" ", "T")); // ensure proper Date parsing
}

function isAttendanceEnabled(activity) {
  if (!activity.schedule || !activity.end_time) return false;

  const now = new Date();
  const start = parseDate(activity.schedule);
  const end = parseDate(activity.end_time);

  console.log("Now:", now);
  console.log("Start:", start);
  console.log("End from data:", activity.end_time);
  console.log("Final end time used:", end);
  console.log("Enabled:", now >= start && now <= end);

  return now >= start && now <= end;
}

// ✅ Determine if training has ended
function isTrainingEnded(activity) {
  console.log("Submitting attendance:", {
    trainingID: activity.trainingID,
    key: activity.qrInput.trim(),
  });
  if (!activity.end_time) return false; // If no end_time, assume not ended
  const now = new Date();
  const endTime = new Date(activity.end_time);
  return now > endTime;
}
function isTrainingActive(activity) {
  if (!activity.schedule) return false;

  const now = new Date();
  const start = new Date(activity.schedule);

  // If end_time is missing, assume training lasts 2 hours
  const end = activity.end_time
    ? new Date(activity.end_time)
    : new Date(start.getTime() + 2 * 60 * 60 * 1000);

  // Validate dates
  if (isNaN(start) || isNaN(end)) return false;

  return now >= start && now <= end;
}
// ✅ Attendance submit (fixed)
async function submitAttendance(activity) {
  console.log("Activity object:", activity); // <-- debugging

  const token = localStorage.getItem("token");
  if (!token) {
    alert("Please log in to submit attendance.");
    return;
  }

  if (!activity.qrInput || activity.qrInput.trim() === "") {
    alert("Please enter the attendance code.");
    return;
  }

  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/attendance/checkin", {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      params: {
        trainingID: activity.registrationID, // ✅ use registrationID instead
        key: activity.qrInput.trim(),
      },
    });

    alert(res.data.message || "Attendance recorded successfully!");
    activity.qrInput = "";
    await fetchMyActivities();
  } catch (error) {
    console.error(
      "Attendance submission error:",
      error.response?.data || error
    );
    alert(
      error.response?.data?.message ||
        "Failed to submit attendance. Please check the code."
    );
  }
}

// ✅ Modal controls
function openModal(activity) {
  selectedActivity.value = activity;
  showModal.value = true;
}
function closeModal() {
  showModal.value = false;
  selectedActivity.value = null;
}

// ✅ Manual counter adjuster
function updateCounters(type, increase = true) {
  if (type === "upcoming") {
    upcomingCount.value += increase ? 1 : -1;
  } else if (type === "completed") {
    completedCount.value += increase ? 1 : -1;
  }
}

// ✅ Call on mount
onMounted(fetchMyActivities);
</script>

<template>
  <div class="min-h-screen bg-gray-100 font-poppins">
    <div class="lg:hidden block font-poppins">
      <!-- Row 1: Profile Info -->
      <div class="text-black p-6">
        <div class="flex flex-col items-center mt-4">
          <!-- Avatar -->
          <div class="avatar w-24 h-24 rounded-full bg-white mb-4">
            <img
              src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
              alt="User Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>

          <!-- Name -->
          <h2 class="text-xl font-semibold mb-2">{{ userName }}</h2>

          <!-- Stats -->
          <div
            class="w-full flex items-center justify-center gap-6 mt-2 relative"
          >
            <!-- Upcoming -->
            <div class="relative">
              <div
                class="flex items-center justify-center bg-white shadow-sm rounded-full px-6 py-2"
              >
                <span class="font-semibold text-gray-700">Upcoming</span>
              </div>
              <!-- Floating Bubble -->
              <span
                class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
              >
                {{ upcomingCount }}
              </span>
            </div>

            <!-- Completed -->
            <div class="relative">
              <div
                class="flex items-center justify-center bg-white shadow-sm rounded-full px-6 py-2"
              >
                <span class="font-semibold text-gray-700">Completed</span>
              </div>
              <!-- Floating Bubble -->
              <span
                class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
              >
                {{ completedCount }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-white mx-3 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-2">My Activity</h3>

        <ul class="space-y-4">
          <li
            v-for="activity in activities"
            :key="activity.registrationID || activity.applicationID"
            class="relative p-2 shadow-sm bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer"
          >
            <div>
              <div
                class="flex flex-col gap-2 p-4 rounded cursor-pointer"
                @click="openModal(activity)"
              >
                <!-- Title -->
                <div class="text-sm">
                  <span class="font-semibold">Title:</span> {{ activity.title }}
                </div>

                <!-- Date -->
                <div class="text-sm">
                  <span class="font-semibold">
                    {{
                      activity.type === "training" ? "Schedule:" : "Deadline:"
                    }}
                  </span>
                  {{
                    activity.schedule
                      ? formatDateTime(activity.schedule)
                      : formatDateTime(activity.deadlineOfSubmission) || "—"
                  }}
                </div>

                <!-- Status -->
                <div class="text-sm">
                  <span class="font-semibold">Status:</span>
                  <span
                    :class="{
                      'text-green-600': [
                        'Applied',
                        'Registered',
                        'Completed',
                      ].includes(activity.status),
                      'text-yellow-600': activity.status === 'Ongoing',
                      'text-red-600': activity.status === 'Cancelled',
                    }"
                  >
                    {{ activity.status || "N/A" }}
                  </span>
                </div>

                <!-- Requirement / Certificate -->
                <div class="text-sm">
                  <span class="font-semibold">
                    {{
                      activity.type === "career"
                        ? "Requirement:"
                        : "Certificate:"
                    }}
                  </span>
                  <button
                    v-if="activity.type === 'career'"
                    class="ml-2 px-3 py-1 rounded text-white text-sm"
                    :class="
                      activity.requirements
                        ? 'bg-blue-500 hover:bg-blue-600'
                        : 'bg-gray-300 cursor-not-allowed'
                    "
                    :disabled="!activity.requirements"
                  >
                    View Requirement
                  </button>

                  <button
                    v-else
                    class="ml-2 px-3 py-1 rounded text-white text-sm"
                    :class="
                      activity.certificate
                        ? 'bg-green-500 hover:bg-green-600'
                        : 'bg-gray-300 cursor-not-allowed'
                    "
                    :disabled="!activity.certificate"
                  >
                    {{ activity.certificate ? "View Certificate" : "Pending" }}
                  </button>
                </div>

                <!-- Attendance Input (Training only) Mobile View-->
                <div v-if="activity.type === 'training'" class="mt-3">
                  <input
                    v-model="activity.qrInput"
                    type="text"
                    placeholder="Enter attendance code"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :disabled="!isAttendanceEnabled(activity)"
                    @click.stop
                  />
                  <button
                    class="mt-2 w-full text-white py-2 rounded"
                    :class="
                      isAttendanceEnabled(activity)
                        ? 'bg-customButton hover:bg-dark-slate'
                        : 'bg-gray-400 cursor-not-allowed'
                    "
                    :disabled="!isAttendanceEnabled(activity)"
                    @click.stop="submitAttendance(activity)"
                  >
                    Submit Attendance
                  </button>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <!-- 🟣 MODAL -->
      <div
        v-if="showModal"
        class="fixed inset-0 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg relative">
          <button
            @click="closeModal"
            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl"
          >
            ×
          </button>

          <h3 class="text-xl font-semibold mb-2">
            {{ selectedActivity.title }}
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            <strong>Organization:</strong>
            {{ selectedActivity.organizationName }}
          </p>

          <!-- TRAINING DETAILS -->
          <div v-if="selectedActivity.type === 'training'">
            <p><strong>Mode:</strong> {{ selectedActivity.mode }}</p>
            <p>
              <strong>Schedule:</strong>
              {{ formatDateTime(selectedActivity.schedule) }}
            </p>
            <p><strong>Location:</strong> {{ selectedActivity.location }}</p>
            <p v-if="selectedActivity.trainingLink">
              <strong>Training Link:</strong>
              <a
                :href="selectedActivity.trainingLink"
                target="_blank"
                class="text-blue-600 underline"
              >
                {{ selectedActivity.trainingLink }}
              </a>
            </p>
            <p><strong>Status:</strong> {{ selectedActivity.status }}</p>

            <!-- QR Code Section -->
            <div
              v-if="selectedActivity.attendance_key"
              class="mt-6 text-center border-t pt-4"
            >
              <p class="text-sm font-semibold mb-2">
                Scan this QR Code for Attendance
              </p>

              <QrcodeVue
                :value="`http://127.0.0.1:8000/attendance?trainingID=${selectedActivity.trainingID}&key=${selectedActivity.attendance_key}`"
                :size="200"
                level="H"
                class="mx-auto"
              />

              <p class="text-gray-500 text-xs mt-2">
                Expires at: {{ formatDateTime(selectedActivity.end_time) }}
              </p>
            </div>

            <!-- No QR yet -->
            <div v-else class="mt-6 text-center text-gray-400 border-t pt-4">
              <p>
                QR Code not available yet. It will appear once the training
                starts.
              </p>
            </div>
          </div>

          <!-- CAREER DETAILS -->
          <div v-else-if="selectedActivity.type === 'career'">
            <p>
              <strong>Details:</strong>
              {{ selectedActivity.detailsAndInstructions }}
            </p>
            <p>
              <strong>Qualifications:</strong>
              {{ selectedActivity.qualifications }}
            </p>
            <p>
              <strong>Requirements:</strong> {{ selectedActivity.requirements }}
            </p>
            <p>
              <strong>Deadline:</strong>
              {{ formatDateTime(selectedActivity.deadlineOfSubmission) }}
            </p>
            <p><strong>Status:</strong> {{ selectedActivity.status }}</p>
          </div>
        </div>
      </div>
    </div>

    <!--Large screen-->
    <div class="min-h-screen p-3 font-poppins hidden lg:flex">
      <!-- Left Column -->
      <div
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center"
      >
        <!-- Avatar -->
        <div class="w-24 h-24 rounded-full bg-white mb-4">
          <div class="avatar w-24 h-24 rounded-full bg-white mb-4">
            <img
              src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
              alt="User Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
        </div>

        <!-- Name -->
        <h2 class="text-xl font-semibold mb-6">{{ userName }}</h2>
        <div
          class="w-full flex items-center justify-center gap-6 mb-6 relative"
        >
          <!-- Upcoming -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Upcoming</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ upcomingCount }}
            </span>
          </div>

          <!-- Completed -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Completed</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ completedCount }}
            </span>
          </div>
        </div>

        <!-- Buttons -->
        <div class="w-full flex flex-col gap-3">
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'ResumeEditorpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 34 34"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Resume</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Certificatespage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 35 35"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Certificates</span>
          </button>

          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Bookmarkpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 31 30"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 3.75V30L17.4375 20.625L27.125 30V3.75H7.75ZM23.25 0H3.875V26.25L5.8125 24.375V1.875H23.25V0Z"
                fill="white"
              />
            </svg>

            <span>Bookmark</span>
          </button>
          <div class="divider"></div>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'UpdateDeletepage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
                fill="white"
              />
            </svg>

            <span>Account Setting</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="logout"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.24 22.27H15.11C10.67 22.27 8.53002 20.52 8.16002 16.6C8.12002 16.19 8.42002 15.82 8.84002 15.78C9.24002 15.74 9.62002 16.05 9.66002 16.46C9.95002 19.6 11.43 20.77 15.12 20.77H15.25C19.32 20.77 20.76 19.33 20.76 15.26V8.73998C20.76 4.66998 19.32 3.22998 15.25 3.22998H15.12C11.41 3.22998 9.93002 4.41998 9.66002 7.61998C9.61002 8.02998 9.26002 8.33998 8.84002 8.29998C8.42002 8.26998 8.12001 7.89998 8.15001 7.48998C8.49001 3.50998 10.64 1.72998 15.11 1.72998H15.24C20.15 1.72998 22.25 3.82998 22.25 8.73998V15.26C22.25 20.17 20.15 22.27 15.24 22.27Z"
                fill="white"
              />
              <path
                d="M15.0001 12.75H3.62012C3.21012 12.75 2.87012 12.41 2.87012 12C2.87012 11.59 3.21012 11.25 3.62012 11.25H15.0001C15.4101 11.25 15.7501 11.59 15.7501 12C15.7501 12.41 15.4101 12.75 15.0001 12.75Z"
                fill="white"
              />
              <path
                d="M5.84994 16.1C5.65994 16.1 5.46994 16.03 5.31994 15.88L1.96994 12.53C1.67994 12.24 1.67994 11.76 1.96994 11.47L5.31994 8.12003C5.60994 7.83003 6.08994 7.83003 6.37994 8.12003C6.66994 8.41003 6.66994 8.89003 6.37994 9.18003L3.55994 12L6.37994 14.82C6.66994 15.11 6.66994 15.59 6.37994 15.88C6.23994 16.03 6.03994 16.1 5.84994 16.1Z"
                fill="white"
              />
            </svg>

            <span>Logout</span>
          </button>
        </div>
      </div>

      <!-- Right Column -->
      <div class="w-full lg:w-3/4 lg:pl-6 mt-6 lg:mt-0 flex flex-col gap-6">
        <!-- Bottom Row: Event Table -->
        <div class="bg-white rounded-lg shadow p-6 flex-1">
          <h3 class="text-2xl font-semibold mb-4">My Activity</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                  >
                    Title
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                  >
                    Date/Time
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                  >
                    Requirement/Certificate
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                  >
                    Attendance
                  </th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="activity in activities"
                  :key="activity.title"
                  class="hover:bg-gray-100"
                  @click="openModal(activity)"
                >
                  <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                    {{ activity.title }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-700">
                    {{
                      activity.schedule
                        ? formatDateTime(activity.schedule)
                        : formatDateTime(activity.deadlineOfSubmission) || "—"
                    }}
                  </td>

                  <td
                    class="px-6 py-4 text-sm font-medium"
                    :class="{
                      'text-green-600':
                        activity.status === 'Registered' ||
                        activity.status === 'Applied',
                      'text-yellow-600': activity.status === 'Scheduled',
                      'text-red-600': activity.status === 'Cancelled',
                    }"
                  >
                    {{ activity.status }}
                  </td>

                  <!-- Requirements / Certificate -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button
                      v-if="activity.type === 'career'"
                      :disabled="!activity.requirements"
                      :class="[
                        'px-3 py-1 rounded text-white',
                        activity.requirements
                          ? 'bg-blue-500 hover:bg-blue-600'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                    >
                      View Requirement
                    </button>

                    <button
                      v-else
                      :disabled="!activity.certificate"
                      :class="[
                        'px-3 py-1 rounded text-white',
                        activity.certificate
                          ? 'bg-green-500 hover:bg-green-600'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                    >
                      {{
                        activity.certificate
                          ? "View Certificate"
                          : "Certificate Pending"
                      }}
                    </button>
                  </td>

                  <!-- Attendance input (training only) Desktop -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div v-if="activity.type === 'training'" class="flex gap-2">
                      <input
                        v-model="activity.qrInput"
                        type="text"
                        placeholder="Enter attendance code"
                        class="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        :disabled="!isAttendanceEnabled(activity)"
                      />
                      <button
                        @click="submitAttendance(activity)"
                        :class="[
                          'px-3 py-1 text-white rounded',
                          isAttendanceEnabled(activity)
                            ? 'bg-customButton hover:bg-blue-600'
                            : 'bg-gray-400 cursor-not-allowed',
                        ]"
                        :disabled="!isAttendanceEnabled(activity)"
                      >
                        Submit
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- 🟣 MODAL -->
            <div
              v-if="showModal"
              class="fixed inset-0 flex items-center justify-center z-50"
            >
              <div
                class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg relative"
              >
                <button
                  @click="closeModal"
                  class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl"
                >
                  ×
                </button>

                <h3 class="text-xl font-semibold mb-2">
                  {{ selectedActivity.title }}
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                  <strong>Organization:</strong>
                  {{ selectedActivity.organizationName }}
                </p>

                <!-- TRAINING DETAILS -->
                <div v-if="selectedActivity.type === 'training'">
                  <p><strong>Mode:</strong> {{ selectedActivity.mode }}</p>
                  <p>
                    <strong>Schedule:</strong>
                    {{ formatDateTime(selectedActivity.schedule) }}
                  </p>
                  <p>
                    <strong>Location:</strong> {{ selectedActivity.location }}
                  </p>
                  <p v-if="selectedActivity.trainingLink">
                    <strong>Training Link:</strong>
                    <a
                      :href="selectedActivity.trainingLink"
                      target="_blank"
                      class="text-blue-600 underline"
                    >
                      {{ selectedActivity.trainingLink }}
                    </a>
                  </p>
                  <p><strong>Status:</strong> {{ selectedActivity.status }}</p>

                  <!-- QR Code Section -->
                  <div
                    v-if="selectedActivity.attendance_key"
                    class="mt-6 text-center border-t pt-4"
                  >
                    <p class="text-sm font-semibold mb-2">
                      Scan this QR Code for Attendance
                    </p>

                    <QrcodeVue
                      :value="`http://127.0.0.1:8000/attendance?trainingID=${selectedActivity.trainingID}&key=${selectedActivity.attendance_key}`"
                      :size="200"
                      level="H"
                      class="mx-auto"
                    />

                    <p class="text-gray-500 text-xs mt-2">
                      Expires at:
                      {{ formatDateTime(selectedActivity.end_time) }}
                    </p>
                  </div>

                  <!-- No QR yet -->
                  <div
                    v-else
                    class="mt-6 text-center text-gray-400 border-t pt-4"
                  >
                    <p>
                      QR Code not available yet. It will appear once the
                      training starts.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
