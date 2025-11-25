<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const toasts = ref([]);
const userName = ref("Guest");
const activities = ref([]);
const upcomingCount = ref(0);
const completedCount = ref(0);
const careerModal = ref(null);
const trainingModal = ref(null);
const selectedCareer = ref(null);
const selectedTraining = ref(null);
const expandedCard = ref(null);
const expandedRow = ref(null);
const activeTab = ref("career"); // default tab

function toggleRow(activity) {
  expandedRow.value = expandedRow.value === activity ? null : activity;
}
function toggleCard(id) {
  expandedCard.value = expandedCard.value === id ? null : id;
}

function showToast(message, type = "success", duration = 3000) {
  const id = Date.now();
  toasts.value.push({ id, message, type });

  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, duration);
}
async function openModal(activity) {
  if (activity.type === "career") {
    try {
      const token = localStorage.getItem("token");
      if (!token) return console.error("❌ No token found.");

      // Fetch all applications for the user
      const res = await axios.get(
        import.meta.env.VITE_API_BASE_URL + "/applications",
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      console.log("🟢 Activity clicked:", activity);
      console.log("Applications from API:", res.data);

      // Find the full application for this career

      const app = res.data.find(
        (a) => a.applicationID === activity.applicationID
      );

      if (app) {
        console.log("🎯 Matching application found:", app);
        selectedCareer.value = {
          ...app,
          ...Object.fromEntries(
            Object.entries(activity).filter(([_, v]) => v !== "" && v !== null)
          ),
        };
      } else {
        console.log("ℹ️ No matching application found — showing career info.");
        selectedCareer.value = activity;
      }

      await nextTick();
      if (careerModal.value) careerModal.value.showModal();
    } catch (err) {
      console.error("❌ Error fetching application:", err);
    }
  } else if (activity.type === "training") {
    await openTrainingModal(activity);
  } else {
    console.warn("⚠️ Unknown activity type:", activity);
  }
}

async function openCareerModal(career) {
  try {
    const token = localStorage.getItem("token");
    if (!token) return console.error("❌ No token found.");

    // ✅ Fetch all applications for the logged-in applicant
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    console.log("✅ Application list from backend:", res.data);

    // 🔍 Find if the user applied to this career
    const app = res.data.find((a) => a.careerID === career.id);
    selectedCareer.value = app || career; // app has the interview details

    if (app) {
      console.log("🎯 Career + interview details found:", app);
      selectedCareer.value = app;
    } else {
      console.log("ℹ️ No application found — showing basic career info.");
      selectedCareer.value = career;
    }

    await nextTick();
    if (careerModal.value) careerModal.value.showModal();
  } catch (err) {
    console.error("❌ Error opening career modal:", err);
  }
}

// ✅ Function to open training modal
async function openTrainingModal(training) {
  try {
    const token = localStorage.getItem("token");
    if (!token) return console.error("❌ No token found.");

    const id = training.id || training.trainingID;
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/trainings/${id}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    selectedTraining.value = res.data;

    await nextTick();
    if (trainingModal.value) trainingModal.value.showModal();
  } catch (err) {
    console.error("❌ Error opening training modal:", err);
  }
}

function closeCareerModal() {
  if (careerModal.value && careerModal.value.open) {
    careerModal.value.close();
  }
}

function closeTrainingModal() {
  if (trainingModal.value && trainingModal.value.open) {
    trainingModal.value.close();
  }
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
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/my-activities/${user.applicantID}`
    );
    activities.value = res.data.activities || [];

    console.log("Raw activities data:", activities.value);
    // ✅ Count by status
    upcomingCount.value = activities.value.filter((a) =>
      ["upcoming", "registered"].includes(a.status?.toLowerCase())
    ).length;

    completedCount.value = activities.value.filter(
      (a) => a.status?.toLowerCase() === "completed"
    ).length;

    console.log("Activities fetched:", activities.value);
  } catch (error) {
    console.error("Error fetching activities:", error);
  }
}

// ✅ Manual counter adjuster
function updateCounters(type, increase = true) {
  if (type === "upcoming") {
    upcomingCount.value += increase ? 1 : -1;
  } else if (type === "completed") {
    completedCount.value += increase ? 1 : -1;
  }
}

const downloadRequirement = async (activity, event) => {
  if (event) {
    event.stopPropagation(); // prevent parent modal opening
    event.preventDefault(); // prevent any default behavior
  }

  const applicationID = activity.applicationID;
  const token = localStorage.getItem("token");

  if (!token) {
    showToast("Please log in to download requirements.");
    return;
  }

  if (!applicationID) {
    console.error("Application ID not found in activity:", activity);
    showToast("Application ID not found. Please try refreshing the page.");
    return;
  }

  // If requirement_directory is not in activity, try to fetch it from the API
  let filePath = activity.requirement_directory;
  console.log("Activity data:", activity);
  console.log("Initial filePath from activity:", filePath);

  // Check if filePath is null, undefined, or empty string
  if (!filePath || filePath === null || filePath === "") {
    console.log("requirement_directory not in activity, fetching from API...");
    try {
      const appResponse = await axios.get(
        `${import.meta.env.VITE_API_BASE_URL}/applications`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      console.log("Applications API response:", appResponse.data);

      const application = appResponse.data.find(
        (app) => app.applicationID === applicationID
      );

      console.log("Found application:", application);

      if (application) {
        filePath = application.requirement_directory;
        console.log("filePath from API:", filePath);

        // If still no filePath, try using the backend endpoint directly
        if (!filePath || filePath === null || filePath === "") {
          console.log("No filePath found, trying backend endpoint directly...");
          // We'll handle this in the try block below by using the backend endpoint
        }
      } else {
        console.error(
          "Application not found for applicationID:",
          applicationID
        );
        // Try backend endpoint anyway - it might have the file
        filePath = null;
      }
    } catch (error) {
      console.error("Error fetching application details:", error);
      // Don't return here - try the backend endpoint as fallback
      filePath = null;
    }
  }

  try {
    // If filePath is provided (from Supabase), use it directly
    if (filePath && filePath !== null && filePath !== "") {
      try {
        // Import getPDFUrl dynamically
        const { getPDFUrl } = await import("@/lib/supabase");
        const pdfUrl = getPDFUrl(filePath, "Requirements");

        // Fetch the PDF from Supabase
        const response = await fetch(pdfUrl);
        if (!response.ok) {
          throw new Error("Failed to fetch PDF from Supabase");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);

        // Extract filename from path or use default
        const fileName = filePath.split("/").pop() || "requirement.pdf";

        const a = document.createElement("a");
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        return; // Success, exit early
      } catch (supabaseError) {
        console.warn(
          "Supabase download failed, trying backend endpoint:",
          supabaseError
        );
        // Fall through to backend endpoint
      }
    }

    // Fallback to backend endpoint (works even if filePath is missing - backend will find it)
    try {
      const response = await axios({
        url: `${
          import.meta.env.VITE_API_BASE_URL
        }/applications/${applicationID}/requirement`,
        method: "GET",
        responseType: "blob",
        headers: { Authorization: `Bearer ${token}` },
      });

      const url = window.URL.createObjectURL(
        new Blob([response.data], { type: "application/pdf" })
      );

      const a = document.createElement("a");
      a.href = url;
      a.download = "requirement.pdf";
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (backendError) {
      console.error("Backend endpoint also failed:", backendError);
      if (backendError.response?.status === 404) {
        showToast(
          "No requirement file has been uploaded for this application."
        );
      } else {
        showToast("Failed to download requirements. Please try again.");
      }
    }
  } catch (error) {
    console.error("Error downloading requirements:", error);
    showToast("Failed to download requirements. Please try again.");
  }
};

const downloadCertificate = async (activity, event) => {
  if (event) {
    event.stopPropagation(); // prevent parent modal opening
    event.preventDefault(); // prevent any default behavior
  }

  const registrationID = activity.registrationID;
  const token = localStorage.getItem("token");

  if (!token) {
    showToast("Please log in to download certificates.");
    return;
  }

  if (!registrationID) {
    console.error("Registration ID not found in activity:", activity);
    showToast("Registration ID not found. Please try refreshing the page.");
    return;
  }

  // Get certificate path from activity
  let filePath = activity.certificate;
  console.log("Activity data:", activity);
  console.log("Initial certificate path from activity:", filePath);

  // If certificate path is not in activity, try to fetch it from the API
  if (!filePath || filePath === null || filePath === "") {
    console.log("certificate not in activity, fetching from API...");
    try {
      // Try to fetch registrations to get certificate
      const regResponse = await axios.get(
        `${import.meta.env.VITE_API_BASE_URL}/registrations`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      console.log("Registrations API response:", regResponse.data);

      const registration = regResponse.data.find(
        (reg) =>
          reg.registrationID === registrationID || reg.id === registrationID
      );

      console.log("Found registration:", registration);

      if (registration) {
        filePath = registration.certificatePath;
        console.log("certificate path from API:", filePath);
      }
    } catch (error) {
      console.error("Error fetching registration details:", error);
      // Continue to try downloading anyway
    }
  }

  // Final check
  if (!filePath || filePath === null || filePath === "") {
    showToast("No certificate has been issued for this training yet.");
    return;
  }

  try {
    // If filePath is provided (from Supabase), use it directly
    if (filePath) {
      try {
        // Import getPDFUrl dynamically
        const { getPDFUrl } = await import("@/lib/supabase");
        // Certificates are stored in "Requirements" bucket
        const pdfUrl = getPDFUrl(filePath, "Requirements");

        // Fetch the PDF from Supabase
        const response = await fetch(pdfUrl);
        if (!response.ok) {
          throw new Error("Failed to fetch certificate from Supabase");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);

        // Extract filename from path or use default
        const fileName =
          filePath.split("/").pop() ||
          `certificate_${activity.title || "training"}.pdf`;

        const a = document.createElement("a");
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        return; // Success
      } catch (supabaseError) {
        console.warn("Supabase download failed:", supabaseError);
        showToast(
          "Failed to download certificate from Supabase. Please try again."
        );
      }
    }
  } catch (error) {
    console.error("Error downloading certificate:", error);
    showToast("Failed to download certificate. Please try again.");
  }
};

onMounted(fetchMyActivities);
</script>

<template>
  <div class="bg-gray-100 font-poppins">
    <div class="lg:hidden block font-poppins">
      <div class="bg-white p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-2">My Activity</h3>

        <ul class="space-y-4">
          <li
            v-for="activity in activities"
            :key="activity.registrationID || activity.applicationID"
            class="relative p-2 shadow-sm bg-blue-gray rounded-lg transition cursor-pointer"
          >
            <!-- Card Header -->
            <div
              class="flex flex-col gap-2 p-4 rounded"
              @click="
                toggleCard(activity.registrationID || activity.applicationID)
              "
            >
              <!-- Title -->
              <div class="text-sm">
                <span class="font-semibold">{{ activity.title }}</span>
              </div>

              <!-- Date -->
              <div class="text-sm">
                <span class="font-semibold">
                  {{ activity.type === "training" ? "Schedule:" : "Deadline:" }}
                </span>
                {{
                  activity.schedule
                    ? formatDateTime(activity.schedule)
                    : formatDateTime(activity.deadlineOfSubmission) || "—"
                }}
              </div>

              <!-- Status -->
              <div class="text-sm">
                <span class="font-semibold">Status: </span>
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
            </div>

            <!-- ▼ Dropdown Content -->
            <transition name="fade-slide">
              <div
                v-if="
                  expandedCard ===
                  (activity.registrationID || activity.applicationID)
                "
                class="mt-2 p-3 bg-white rounded-lg shadow-inner text-sm"
              >
                <!-- Career Dropdown -->
                <template v-if="activity.type === 'career'">
                  <p>
                    <strong>Details:</strong>
                    {{ activity.detailsAndInstructions }}
                  </p>
                  <p>
                    <strong>Qualifications:</strong>
                    {{ activity.qualifications }}
                  </p>
                  <p>
                    <strong>Requirement:</strong> {{ activity.requirements }}
                  </p>
                  <p>
                    <strong>Deadline:</strong>
                    {{ formatDateTime(activity.deadlineOfSubmission) }}
                  </p>

                  <button
                    type="button"
                    :class="[
                      'mt-3 px-3 py-1 rounded text-white',
                      activity.requirement_directory || activity.applicationID
                        ? 'bg-blue-500 hover:bg-blue-600'
                        : 'bg-gray-300 cursor-not-allowed',
                    ]"
                    @click.stop="downloadRequirement(activity, $event)"
                  >
                    Download Requirement
                  </button>

                  <div
                    v-if="!selectedCareer.interviewSchedule"
                    class="divider"
                  ></div>
                  <p class="text-center text-gray-500 text-sm mb-2">
                    Application is still being processed.
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
                    <p v-if="selectedCareer.interviewSchedule">
                      <strong>Interview Schedule:</strong>
                      {{ formatDateTime(selectedCareer.interviewSchedule) }}
                    </p>
                    <p v-if="selectedCareer.interviewMode">
                      <strong>Mode:</strong> {{ selectedCareer.interviewMode }}
                    </p>
                    <p v-if="selectedCareer.interviewLink">
                      <strong>Link:</strong>
                      <a :href="selectedCareer.interviewLink" target="_blank">
                        {{ selectedCareer.interviewLink }}
                      </a>
                    </p>
                    <p v-if="selectedCareer.interviewLocation">
                      <strong>Location:</strong>
                      {{ selectedCareer.interviewLocation }}
                    </p>
                  </div>
                </template>

                <!-- Training Dropdown -->
                <template v-else>
                  <p><strong>Mode:</strong> {{ activity.mode }}</p>
                  <p><strong>Details:</strong> {{ activity.description }}</p>

                  <p v-if="activity.location">
                    <strong>Location:</strong> {{ activity.location }}
                  </p>

                  <p v-if="activity.trainingLink">
                    <strong>Link:</strong>
                    <a
                      :href="activity.trainingLink"
                      target="_blank"
                      class="text-blue-500 underline"
                    >
                      {{ activity.trainingLink }}
                    </a>
                  </p>

                  <p>
                    <strong>Schedule:</strong>
                    {{ formatDateTime(activity.schedule) }}
                  </p>

                  <button
                    type="button"
                    class="mt-3 px-3 py-1 rounded text-white"
                    :class="[
                      activity.status?.toLowerCase() === 'attended'
                        ? 'bg-green-500 hover:bg-green-600'
                        : 'bg-gray-300 cursor-not-allowed',
                    ]"
                    :disabled="activity.status !== 'Attended'"
                    @click.stop="downloadCertificate(activity, $event)"
                  >
                    {{
                      activity.status === "Attended"
                        ? "Download Certificate"
                        : "Certificate Unavailable"
                    }}
                  </button>
                </template>
              </div>
            </transition>
          </li>
        </ul>
      </div>
    </div>

    <!--Large screen-->
    <div class="min-h-screen p-3 font-poppins hidden lg:flex flex-col gap-4">
      <!-- My Activity Container -->
      <div class="bg-white rounded-lg shadow p-6 flex-1">
        <h3 class="text-2xl font-semibold mb-4">My Activity</h3>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-4">
          <button
            class="px-4 py-2 -mb-px font-semibold text-gray-700 border-b-2"
            :class="{
              'border-blue-500 text-blue-500': activeTab === 'career',
              'border-transparent hover:text-blue-500': activeTab !== 'career',
            }"
            @click="activeTab = 'career'"
          >
            Career
          </button>
          <button
            class="px-4 py-2 -mb-px font-semibold text-gray-700 border-b-2"
            :class="{
              'border-blue-500 text-blue-500': activeTab === 'training',
              'border-transparent hover:text-blue-500':
                activeTab !== 'training',
            }"
            @click="activeTab = 'training'"
          >
            Training
          </button>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                >
                  Title
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                >
                  Date/Time
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider"
                >
                  Requirement/Certificate
                </th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <template
                v-for="activity in activities.filter(
                  (a) => a.type === activeTab
                )"
                :key="activity.title"
              >
                <!-- MAIN ROW -->
                <tr
                  class="hover:bg-gray-100 cursor-pointer"
                  @click="toggleRow(activity)"
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

                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button
                      v-if="activity.type === 'career'"
                      type="button"
                      :class="[
                        'px-3 py-1 rounded text-white',
                        activity.requirement_directory || activity.applicationID
                          ? 'bg-blue-500 hover:bg-blue-600 cursor-pointer'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                      @click.stop="downloadRequirement(activity, $event)"
                    >
                      Download Requirement
                    </button>

                    <button
                      v-else
                      type="button"
                      :class="[
                        'px-3 py-1 rounded text-white',
                        activity.status?.toLowerCase() === 'attended'
                          ? 'bg-green-500 hover:bg-green-600 cursor-pointer'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                      :disabled="activity.status?.toLowerCase() !== 'attended'"
                      @click.stop="
                        activity.status?.toLowerCase() === 'attended'
                          ? downloadCertificate(activity, $event)
                          : null
                      "
                    >
                      {{
                        activity.status?.toLowerCase() === "attended"
                          ? "Download Certificate"
                          : "Certificate Unavailable"
                      }}
                    </button>
                  </td>
                </tr>

                <!-- ▼ DROPDOWN DETAILS -->
                <tr v-if="expandedRow === activity" class="bg-gray-50">
                  <td colspan="4" class="px-6 py-4">
                    <!-- CAREER DETAILS -->
                    <div v-if="activity.type === 'career'">
                      <h3 class="text-lg font-bold mb-1">
                        {{ activity.title || activity.career?.position }}
                      </h3>

                      <p class="text-sm text-gray-600 mb-1">
                        <strong>Organization:</strong>
                        {{ activity.organizationName || activity.organization }}
                      </p>

                      <p>
                        <strong>Details:</strong>
                        {{
                          activity.detailsAndInstructions ||
                          activity.career?.detailsAndInstructions
                        }}
                      </p>

                      <p>
                        <strong>Qualifications:</strong>
                        {{
                          activity.qualifications ||
                          activity.career?.qualifications
                        }}
                      </p>

                      <p>
                        <strong>Requirements:</strong>
                        {{
                          activity.requirements || activity.career?.requirements
                        }}
                      </p>

                      <p>
                        <strong>Application Address:</strong>
                        {{
                          activity.applicationLetterAddress ||
                          activity.career?.applicationLetterAddress
                        }}
                      </p>

                      <p>
                        <strong>Deadline:</strong>
                        {{
                          formatDateTime(
                            activity.deadlineOfSubmission ||
                              activity.career?.deadlineOfSubmission
                          )
                        }}
                      </p>

                      <div
                        v-if="!activity.interviewSchedule"
                        class="divider"
                      ></div>
                      <p class="text-center text-gray-500 text-sm mb-2">
                        Application is still being processed.
                      </p>

                      <div
                        v-if="
                          activity.interviewSchedule ||
                          activity.interviewMode ||
                          activity.interviewLink ||
                          activity.interviewLocation
                        "
                      >
                        <p v-if="activity.interviewSchedule">
                          <strong>Interview Schedule:</strong>
                          {{ formatDateTime(activity.interviewSchedule) }}
                        </p>

                        <p v-if="activity.interviewMode">
                          <strong>Mode:</strong> {{ activity.interviewMode }}
                        </p>

                        <p v-if="activity.interviewLink">
                          <strong>Link:</strong>
                          <a
                            :href="activity.interviewLink"
                            target="_blank"
                            class="text-blue-500 underline"
                          >
                            {{ activity.interviewLink }}
                          </a>
                        </p>

                        <p v-if="activity.interviewLocation">
                          <strong>Location:</strong>
                          {{ activity.interviewLocation }}
                        </p>
                      </div>
                    </div>

                    <!-- TRAINING DETAILS -->
                    <div v-else>
                      <h3 class="text-lg font-bold mb-1">
                        {{ activity.title }}
                      </h3>

                      <p class="text-sm text-gray-600 mb-1">
                        <strong>Organization:</strong>
                        {{ activity.organizationName }}
                      </p>

                      <p><strong>Mode:</strong> {{ activity.mode }}</p>
                      <p>
                        <strong>Details:</strong> {{ activity.description }}
                      </p>

                      <p v-if="activity.location">
                        <strong>Location:</strong> {{ activity.location }}
                      </p>

                      <p v-if="activity.trainingLink">
                        <strong>Link:</strong>
                        <a
                          :href="activity.trainingLink"
                          target="_blank"
                          class="text-blue-500 underline"
                        >
                          {{ activity.trainingLink }}
                        </a>
                      </p>

                      <p>
                        <strong>Schedule:</strong>
                        {{ formatDateTime(activity.schedule) }}
                      </p>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="fixed top-5 right-5 space-y-2 z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'px-4 py-2 rounded shadow ',
          toast.type === 'success'
            ? 'bg-white text-black'
            : toast.type === 'error'
            ? 'bg-red-500 text-white'
            : 'bg-gray-500',
        ]"
      >
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>
<style>
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-5px);
}
.fade-slide-enter-to {
  opacity: 1;
  transform: translateY(0);
}
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}
.fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>
