<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { getImageUrl, getPDFUrl } from "@/lib/supabase";

const router = useRouter();
const toasts = ref([]);
const userName = ref("Guest");
const userEmail = ref("");
const profileAvatar = ref("");
const upcomingCount = ref(0);
const completedCount = ref(0);
const careerModal = ref(null);
const trainingModal = ref(null);
const selectedCareer = ref(null);
const selectedTraining = ref(null);
const expandedCard = ref(null);
const expandedRow = ref(null);
const activeTab = ref("career"); // default tab

const careerActivities = ref([]);
const trainingActivities = ref([]);

const filteredActivities = computed(() => {
  return activeTab.value === "training"
    ? trainingActivities.value
    : careerActivities.value;
});

const currentSteps = computed(() =>
  activeTab.value === "training" ? TRAINING_STEPS : CAREER_STEPS
);

const profileFallbackAvatar = computed(() => {
  const name = userName.value || "User";
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(
    name
  )}&background=0D8ABC&color=fff`;
});

const displayProfileAvatar = computed(
  () => profileAvatar.value || profileFallbackAvatar.value
);
function resolveAvatarUrl(path) {
  if (!path) return "";
  if (path.startsWith("http")) return path;
  return getImageUrl(path, "Requirements") || "";
}

function resolvePDFUrl(path) {
  if (!path) return "";
  // If it's already a full URL, return it
  if (path.startsWith("http")) return path;
  // Otherwise, convert the path to a public URL using getPDFUrl
  return getPDFUrl(path, "Requirements") || path;
}

function applyUserProfile(user) {
  if (!user) return;
  if (user.firstName && user.lastName) {
    userName.value = `${user.firstName} ${user.lastName}`.trim();
  }
  userEmail.value =
    user.emailAddress ||
    user.email ||
    user.email_address ||
    user.EmailAddress ||
    "";

  const path =
    user.displayPicture_directory ||
    user.DisplayPicture_directory ||
    user.displaypicture_directory ||
    "";
  const directUrl = user.profilePicture || user.profile_picture || "";

  if (path) {
    const url = resolveAvatarUrl(path);
    if (url) {
      profileAvatar.value = url;
      return;
    }
  }

  if (directUrl) {
    profileAvatar.value = directUrl;
  }
}

const handleAvatarEvent = (event) => {
  const detail = event.detail;
  if (!detail) return;
  if (typeof detail === "string") {
    profileAvatar.value = detail;
    return;
  }
  if (detail.url) profileAvatar.value = detail.url;
};


const CAREER_STEPS = [
  { key: "applied", label: "For Review", dateField: "appliedDate" },
  {
    key: "screen",
    label: "For Interview",
    dateField: "interviewSchedule",
    fallbackDateField: "screenDate",
  },
  { key: "hired", label: "Hired", dateField: "hiredDate" },
  { key: "declined", label: "Declined", dateField: "declinedDate" },
];

const TRAINING_STEPS = [
  { key: "registered", label: "Registered", dateField: "registeredDate" },
  { key: "ongoing", label: "Ongoing", dateField: "ongoingDate" },
  { key: "completed", label: "Completed", dateField: "completedDate" },
  { key: "certified", label: "Certified", dateField: "certifiedDate" },
];

function getStatusSteps(activity) {
  if (!activity) return CAREER_STEPS;
  return activity.type === "training" ? TRAINING_STEPS : CAREER_STEPS;
}

function getCurrentStepIndex(activity) {
  const statusRaw = activity?.status || "";
  const status = statusRaw.toLowerCase();
  const steps = getStatusSteps(activity).map((s) => s.key.toLowerCase());

  if (activity?.type === "training") {
    const normalized = status.replace(/[\s-]+/g, "");
    const registeredIdx = steps.indexOf("registered");
    if (!normalized) return registeredIdx;

    if (normalized.includes("cert")) {
      return steps.indexOf("certified");
    }

    if (normalized.includes("complete") || normalized.includes("attend")) {
      return steps.indexOf("completed");
    }

    if (normalized.includes("ongoing")) {
      return steps.indexOf("ongoing");
    }

    if (normalized.includes("registr")) {
      return steps.indexOf("registered");
    }

    return registeredIdx;
  }

  const idx = steps.indexOf(status);
  if (idx !== -1) return idx;

  const appliedIdx = steps.indexOf("applied");
  const screenIdx = steps.indexOf("screen");
  const hiredIdx = steps.indexOf("hired");
  const declinedIdx = steps.indexOf("declined");

  if ((status.includes("accept") || status.includes("hire")) && hiredIdx !== -1) {
    return hiredIdx;
  }

  if ((status.includes("decline") || status.includes("reject")) && declinedIdx !== -1) {
    return declinedIdx;
  }

  if (
    status.includes("interview") ||
    status.includes("screen") ||
    status.includes("pending")
  ) {
    if (screenIdx !== -1) return screenIdx;
  }

  if (
    status.includes("review") ||
    status.includes("submit") ||
    status.includes("appl")
  ) {
    if (appliedIdx !== -1) return appliedIdx;
  }

  return appliedIdx !== -1 ? appliedIdx : 0;
}

function getCareerActiveKeys(statusRaw) {
  const status = (statusRaw || "").toLowerCase();
  if (!status) return null;

  if (status.includes("declin") || status.includes("reject")) {
    return new Set(["applied", "screen", "declined"]);
  }

  if (status.includes("hire") || status.includes("accept")) {
    return new Set(["applied", "screen", "hired"]);
  }

  if (
    status.includes("pending") ||
    status.includes("interview") ||
    status.includes("screen")
  ) {
    return new Set(["applied", "screen"]);
  }

  if (
    status.includes("review") ||
    status.includes("submit") ||
    status.includes("appl")
  ) {
    return new Set(["applied"]);
  }

  return null;
}

function isStepActive(activity, step, stepIndex) {
  if (!activity) return false;

  if (activity.type === "career") {
    const activeKeys = getCareerActiveKeys(activity.status);
    if (activeKeys) {
      return activeKeys.has(step.key);
    }
  }

  const currentIndex = getCurrentStepIndex(activity);
  return stepIndex <= currentIndex;
}

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
function parseLocalDateTime(value) {
  if (!value) return null;
  if (value instanceof Date) {
    return isNaN(value) ? null : value;
  }

  const raw = String(value).trim();
  if (!raw) return null;

  try {
    let sanitized = raw.replace("T", " ").replace("t", " ");
    sanitized = sanitized.replace(/z$/i, "");
    if (sanitized.includes("+")) {
      sanitized = sanitized.split("+")[0];
    }

    const [datePart, timePart = "00:00:00"] = sanitized.split(" ");
    const [yearStr, monthStr, dayStr] = datePart.split("-");
    const [hourStr = "0", minuteStr = "0", secondStr = "0"] =
      timePart.split(":");

    const year = Number(yearStr);
    const month = Number(monthStr);
    const day = Number(dayStr);
    const hour = Number(hourStr);
    const minute = Number(minuteStr);
    const second = Number(secondStr);

    if ([year, month, day].some((num) => Number.isNaN(num))) {
      throw new Error("Invalid date parts");
    }

    const date = new Date(year, (month || 1) - 1, day || 1, hour, minute, second);
    if (Number.isNaN(date.getTime())) {
      throw new Error("Invalid date");
    }
    return date;
  } catch (error) {
    const fallback = new Date(raw);
    return Number.isNaN(fallback.getTime()) ? null : fallback;
  }
}

// ✅ Format datetime (with AM/PM)
function formatDateTime(dateStr) {
  const date = parseLocalDateTime(dateStr);
  if (!date) return "N/A";

  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

function formatDateOnly(dateStr) {
  const date = parseLocalDateTime(dateStr);
  if (!date) return "N/A";

  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function getPrimarySchedule(activity) {
  if (!activity) return null;
  const schedules = activity.schedules;
  if (Array.isArray(schedules) && schedules.length > 0) {
    return schedules[0];
  }
  return null;
}

function getTrainingDate(activity) {
  if (!activity) return null;
  const primary = getPrimarySchedule(activity);
  return (
    activity.schedule ||
    activity.start_time ||
    primary?.schedule ||
    primary?.start_time ||
    null
  );
}

function getTrainingMode(activity) {
  const primary = getPrimarySchedule(activity);
  return activity.mode || primary?.mode || null;
}

function getTrainingLocation(activity) {
  const primary = getPrimarySchedule(activity);
  return activity.location || primary?.location || null;
}

function getTrainingLink(activity) {
  const primary = getPrimarySchedule(activity);
  return activity.trainingLink || primary?.trainingLink || null;
}

function getRegistrationDate(activity) {
  if (!activity) return null;
  return (
    activity.registeredDate ||
    activity.registrationDate ||
    activity.dateRegistered ||
    activity.registration_date ||
    activity.created_at ||
    activity.createdAt ||
    null
  );
}

function getApplicationDate(activity) {
  if (!activity) return null;
  return (
    activity.appliedDate ||
    activity.dateSubmitted ||
    activity.applicationDate ||
    activity.created_at ||
    activity.createdAt ||
    null
  );
}

function getScheduleList(activity) {
  if (!activity) return [];

  if (Array.isArray(activity.schedules) && activity.schedules.length > 0) {
    return activity.schedules.map((schedule, index) => ({
      id: schedule.trainingScheduleID || `schedule-${index}`,
      start: schedule.schedule || schedule.start_time || null,
      end: schedule.end_time || null,
      mode: schedule.mode || null,
      location: schedule.location || null,
      trainingLink: schedule.trainingLink || null,
    }));
  }

  const primary = getPrimarySchedule(activity);
  const start =
    activity.schedule ||
    activity.start_time ||
    primary?.schedule ||
    primary?.start_time ||
    null;
  const end = activity.end_time || primary?.end_time || null;

  if (start || end) {
    return [
      {
        id: activity.trainingID || "primary",
        start,
        end,
        mode: activity.mode || primary?.mode || null,
        location: activity.location || primary?.location || null,
        trainingLink:
          activity.trainingLink || primary?.trainingLink || null,
      },
    ];
  }

  return [];
}

function formatScheduleRange(entry) {
  return formatScheduleText(entry);
}

function formatScheduleDateRange(entry) {
  if (!entry) return "Schedule not available";
  const startRaw =
    entry.start ?? entry.schedule ?? entry.start_time ?? entry.startTime;
  const endRaw = entry.end ?? entry.end_time ?? entry.endTime;

  const startDate = parseLocalDateTime(startRaw);
  if (!startDate) return "Schedule not available";

  const dateOptions = { year: "numeric", month: "long", day: "numeric" };
  const startLabel = startDate.toLocaleDateString("en-US", dateOptions);

  if (endRaw) {
    const endDate = parseLocalDateTime(endRaw);
    if (!endDate) return startLabel;

    const sameDay =
      startDate.getFullYear() === endDate.getFullYear() &&
      startDate.getMonth() === endDate.getMonth() &&
      startDate.getDate() === endDate.getDate();

    if (sameDay) {
      return startLabel;
    }

    const endLabel = endDate.toLocaleDateString("en-US", dateOptions);
    return `${startLabel} - ${endLabel}`;
  }

  return startLabel;
}

function formatScheduleText(entry) {
  if (!entry) return "Schedule not available";
  const startRaw =
    entry.start ?? entry.schedule ?? entry.start_time ?? entry.startTime;
  const endRaw = entry.end ?? entry.end_time ?? entry.endTime;

  const startDate = parseLocalDateTime(startRaw);
  if (!startDate) return "Schedule not available";
  const endDate = endRaw ? parseLocalDateTime(endRaw) : null;

  const dateOptions = { year: "numeric", month: "long", day: "numeric" };
  const timeOptions = { hour: "numeric", minute: "2-digit", hour12: true };

  const startDateLabel = startDate.toLocaleDateString("en-US", dateOptions);
  const startTimeLabel = startDate.toLocaleTimeString("en-US", timeOptions);

  if (endDate) {
    const sameDay =
      startDate.getFullYear() === endDate.getFullYear() &&
      startDate.getMonth() === endDate.getMonth() &&
      startDate.getDate() === endDate.getDate();

    const endTimeLabel = endDate.toLocaleTimeString("en-US", timeOptions);

    if (sameDay) {
      return `${startDateLabel} | ${startTimeLabel} - ${endTimeLabel}`;
    }

    const endDateLabel = endDate.toLocaleDateString("en-US", dateOptions);
    return `${startDateLabel} ${startTimeLabel} - ${endDateLabel} ${endTimeLabel}`;
  }

  return `${startDateLabel} | ${startTimeLabel}`;
}

function formatTime(dateStr) {
  const date = parseLocalDateTime(dateStr);
  if (!date) return "";

  return date.toLocaleTimeString("en-US", {
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
function normalizeTraining(registration) {
  if (!registration) return null;

  const schedules = Array.isArray(registration.training?.schedules)
    ? registration.training.schedules
    : [];
  const primary = schedules[0] || {};
  const organizationName =
    registration.training?.organization?.name || "Unknown";

  return {
    type: "training",
    registrationID: registration.registrationID,
    trainingID: registration.trainingID,
    title: registration.training?.title || "Training",
    description: registration.training?.description || "",
    status:
      registration.registrationStatus ||
      registration.registration_status ||
      "Registered",
    organizationName,
    schedules,
    schedule:
      primary.schedule ||
      primary.start_time ||
      registration.schedule ||
      registration.start_time ||
      null,
    start_time: primary.start_time || registration.start_time || null,
    end_time: primary.end_time || registration.end_time || null,
    mode: primary.mode || registration.mode || null,
    location: primary.location || registration.location || null,
    trainingLink: primary.trainingLink || registration.trainingLink || null,
    certGivenDate: registration.certGivenDate,
    certificate: registration.certificatePath,
    registeredDate:
      registration.registeredDate ||
      registration.registrationDate ||
      registration.dateRegistered ||
      registration.registration_date ||
      registration.created_at ||
      registration.createdAt ||
      registration.updated_at ||
      null,
  };
}

function hasCertificate(activity) {
  if (!activity) return false;
  return Boolean(
    activity.certificate ||
      activity.certificatePath ||
      activity.certGivenDate ||
      activity.certificate_url
  );
}

function getCareerStepDates(application) {
  const history = Array.isArray(application?.statusHistory)
    ? application.statusHistory
    : [];

  const dates = {
    appliedDate: null,
    screenDate: null,
    hiredDate: null,
    declinedDate: null,
  };

  history.forEach((entry) => {
    const key = (entry.status || "").toLowerCase();
    if (!dates.appliedDate && (key.includes("apply") || key.includes("review"))) {
      dates.appliedDate = entry.date;
    } else if (!dates.screenDate && (key.includes("screen") || key.includes("interview"))) {
      dates.screenDate = entry.date;
    } else if (!dates.hiredDate && (key.includes("hire") || key.includes("accept"))) {
      dates.hiredDate = entry.date;
    } else if (!dates.declinedDate && (key.includes("decline") || key.includes("reject"))) {
      dates.declinedDate = entry.date;
    }
  });

  return dates;
}

function normalizeCareer(application) {
  if (!application) return null;

  const orgName =
    application.career?.organization?.name ||
    application.organizationName ||
    "Unknown";

  const stepDates = getCareerStepDates(application);

  return {
    type: "career",
    applicationID: application.applicationID,
    careerID: application.careerID,
    title: application.career?.position || application.title || "Career",
    details: application.career?.details || application.details || "",
    placeOfAssignment:
      application.career?.placeOfAssignment || application.placeOfAssignment,
    qualificationStandard:
      application.career?.qualificationStandard ||
      application.qualificationStandard,
    pdf_directory: application.career?.pdf_directory || application.pdf_directory,
    postingDate: application.career?.postingDate || application.postingDate,
    closingDate: application.career?.closingDate || application.closingDate,
    status:
      application.applicationStatus ||
      application.status ||
      "Submitted",
    requirement_directory:
      application.requirement_directory || application.Requirements || null,
    organizationName: orgName,
    interviewSchedule: application.interviewSchedule,
    interviewMode: application.interviewMode,
    interviewLink: application.interviewLink,
    interviewLocation: application.interviewLocation,
    appliedDate:
      stepDates.appliedDate ||
      application.appliedDate ||
      application.dateSubmitted ||
      application.created_at,
    screenDate: stepDates.screenDate || application.screenDate || null,
    hiredDate: stepDates.hiredDate || application.hiredDate || null,
    declinedDate: stepDates.declinedDate || application.declinedDate || null,
  };
}

function getStepDate(activity, step) {
  if (!activity || !step?.dateField) return null;
  return (
    activity[step.dateField] ||
    (step.fallbackDateField ? activity[step.fallbackDateField] : null) ||
    null
  );
}

async function fetchActivitiesDirectly() {
  const savedUser = localStorage.getItem("user");
  if (!savedUser) return;

  const user = JSON.parse(savedUser);
  applyUserProfile(user);

  try {
  console.log("Fetching registrations and applications for applicantID:", user.applicantID);

    const token = localStorage.getItem("token");
    const headers = token ? { Authorization: `Bearer ${token}` } : {};

    const [registrationsRes, applicationsRes] = await Promise.all([
      axios.get(
        `${import.meta.env.VITE_API_BASE_URL}/registrations`,
        { headers }
      ),
      axios.get(
        `${import.meta.env.VITE_API_BASE_URL}/applications`,
        { headers, params: { applicantID: user.applicantID } }
      ),
    ]);

    const normalizedTrainings = (registrationsRes.data || [])
      .map(normalizeTraining)
      .filter(Boolean);

    const normalizedCareers = (applicationsRes.data || [])
      .map(normalizeCareer)
      .filter(Boolean);

    trainingActivities.value = normalizedTrainings;
    careerActivities.value = normalizedCareers;

    upcomingCount.value = normalizedTrainings.filter((a) =>
      ["registered", "upcoming"].includes(a.status?.toLowerCase())
    ).length;

    completedCount.value = normalizedTrainings.filter(
      (a) => a.status?.toLowerCase() === "attended"
    ).length;

    if (normalizedTrainings.length > 0 && normalizedCareers.length === 0) {
      activeTab.value = "training";
    } else if (normalizedCareers.length > 0 && normalizedTrainings.length === 0) {
      activeTab.value = "career";
    }

    console.log("Trainings fetched:", normalizedTrainings);
    console.log("Careers fetched:", normalizedCareers);
  } catch (error) {
    console.error("Error fetching registrations/applications:", error);
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
    showToast("Please log in to view requirements.");
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
      } else {
        console.error(
          "Application not found for applicationID:",
          applicationID
        );
        filePath = null;
      }
    } catch (error) {
      console.error("Error fetching application details:", error);
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

        if (pdfUrl) {
          // Open PDF in new tab for viewing
          window.open(pdfUrl, "_blank");
          return; // Success, exit early
        } else {
          throw new Error("Failed to generate PDF URL from Supabase");
        }
      } catch (supabaseError) {
        console.warn(
          "Supabase URL generation failed, trying backend endpoint:",
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

      // Create blob URL and open in new tab for viewing
      const blob = new Blob([response.data], { type: "application/pdf" });
      const url = window.URL.createObjectURL(blob);
      
      // Open in new tab
      window.open(url, "_blank");
      
      // Clean up the blob URL after a delay (browser will keep it while tab is open)
      setTimeout(() => {
        window.URL.revokeObjectURL(url);
      }, 100);
    } catch (backendError) {
      console.error("Backend endpoint also failed:", backendError);
      if (backendError.response?.status === 404) {
        showToast(
          "No requirement file has been uploaded for this application."
        );
      } else {
        showToast("Failed to view requirements. Please try again.");
      }
    }
  } catch (error) {
    console.error("Error viewing requirements:", error);
    showToast("Failed to view requirements. Please try again.");
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

onMounted(() => {
  window.addEventListener("profile-avatar-updated", handleAvatarEvent);
  fetchActivitiesDirectly();
});

onBeforeUnmount(() => {
  window.removeEventListener("profile-avatar-updated", handleAvatarEvent);
});
</script>

<template>
  <div class="bg-gray-100 font-poppins">
    <div class="max-w-6xl mx-auto px-3 pt-4">
      <div
        class="bg-white rounded-lg shadow p-6 flex flex-col sm:flex-row items-center gap-4"
      >
        <img
          :src="displayProfileAvatar"
          alt="Profile avatar"
          class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
        />
        <div class="text-center sm:text-left">
          <h2 class="text-2xl font-bold text-gray-800">
            {{ userName || "Guest" }}
          </h2>
          <p class="text-sm text-gray-500">
            {{ userEmail || "No email on file" }}
          </p>
          
        </div>
      </div>
    </div>

    <div class="lg:hidden block font-poppins max-w-6xl mx-auto px-3 mt-4">
      <div class="bg-white p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-2">My Activity</h3>

        <div class="flex border-b border-gray-200 mb-4">
          <button
            class="flex-1 px-3 py-2 -mb-px text-sm font-medium border-b-2"
            :class="{
              'border-blue-500 text-blue-500': activeTab === 'career',
              'border-transparent text-gray-500': activeTab !== 'career',
            }"
            @click="activeTab = 'career'"
          >
            Career
          </button>
          <button
            class="flex-1 px-3 py-2 -mb-px text-sm font-medium border-b-2"
            :class="{
              'border-blue-500 text-blue-500': activeTab === 'training',
              'border-transparent text-gray-500': activeTab !== 'training',
            }"
            @click="activeTab = 'training'"
          >
            Training
          </button>
        </div>

        <p
          v-if="filteredActivities.length === 0"
          class="text-center text-sm text-gray-500 py-6"
        >
          No
          {{
            activeTab === "career"
              ? "career applications"
              : "training registrations"
          }}
          yet.
        </p>

        <ul v-else class="space-y-4">
          <li
            v-for="activity in filteredActivities"
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

              <!-- Career Dates -->
              <div
                v-if="activity.type === 'career'"
                class="text-xs text-gray-600 space-y-1"
              >
                <p>
                  <span class="font-semibold">Posting Date:</span>
                  {{ formatDateOnly(activity.postingDate) }}
                </p>
                <p>
                  <span class="font-semibold">Closing Date:</span>
                  {{ formatDateOnly(activity.closingDate) }}
                </p>
                <p>
                  <span class="font-semibold">Applied Date:</span>
                  {{ formatDateOnly(getApplicationDate(activity)) }}
                </p>
              </div>

              <!-- Training Schedules -->
              <div
                v-else
                class="text-xs text-gray-600 space-y-1"
              >
                <p class="font-semibold">Schedule:</p>
                <template v-if="getScheduleList(activity).length">
                  <div class="pl-3 space-y-1">
                    <p
                      v-for="schedule in getScheduleList(activity)"
                      :key="`mobile-sched-${activity.registrationID || activity.trainingID}-${schedule.id}`"
                    >
                      {{ formatScheduleDateRange(schedule) }}
                    </p>
                  </div>
                </template>
                <p v-else class="pl-3">
                  {{ formatDateOnly(getTrainingDate(activity)) }}
                </p>
              </div>

              <!-- Status -->
              <div class="text-xs">
                <div class="flex gap-3 justify-between">
                  <div
                    v-for="(step, index) in getStatusSteps(activity)"
                    :key="`mobile-step-${activity.title}-${step.key}`"
                    class="flex-1 flex flex-col items-center gap-1"
                  >
                    <span>
                      <span
                        class="inline-flex w-3 h-3 rounded-full border-2"
                        :class="{
                          'border-blue-500 bg-blue-500': isStepActive(
                            activity,
                            step,
                            index
                          ),
                          'border-gray-300 bg-white': !isStepActive(
                            activity,
                            step,
                            index
                          ),
                        }"
                      ></span>
                    </span>
                    <span class="text-[10px] text-gray-600">
                      {{ step.label }}
                    </span>
                    <span class="text-[10px] text-gray-400">
                      {{
                        getStepDate(activity, step)
                          ? formatDateOnly(getStepDate(activity, step))
                          : "—"
                      }}
                    </span>
                  </div>
                </div>
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
                  <p v-if="activity.details" class="career-text-inline">
                    <strong>Details:</strong>
                    <span class="career-text-value">{{ activity.details }}</span>
                  </p>
                  <p>
                    <strong>Place of Assignment:</strong>
                    {{ activity.placeOfAssignment || "Not specified" }}
                  </p>
                  <p v-if="activity.qualificationStandard" class="career-text-inline">
                    <strong>Qualification Standard:</strong>
                    <span class="career-text-value">{{ activity.qualificationStandard }}</span>
                  </p>
                  <p v-if="activity.pdf_directory">
                  
                    <a
                      :href="resolvePDFUrl(activity.pdf_directory)"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-blue-500 underline"
                      @click.stop
                    >
                    View Details
                    </a>
                  </p>
                  <p>
                    <strong>Posting Date:</strong>
                    {{ formatDateTime(activity.postingDate) }}
                  </p>
                  <p>
                    <strong>Closing Date:</strong>
                    {{ formatDateTime(activity.closingDate) }}
                  </p>

                  <div class="mt-4 flex flex-wrap gap-3">
                    <button
                      type="button"
                      :class="[
                        'px-3 py-1 rounded text-white',
                        activity.requirement_directory || activity.applicationID
                          ? 'bg-blue-500 hover:bg-blue-600'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                      @click.stop="downloadRequirement(activity, $event)"
                    >
                      View Requirement
                    </button>
                  </div>
                </template>

                <!-- Training Dropdown -->
                <template v-else>
                  <p><strong>Details:</strong> {{ activity.description }}</p>

                  <div class="mt-3">
                    <p class="text-sm font-semibold text-gray-700">
                      Schedules
                    </p>

                    <div
                      v-if="getScheduleList(activity).length"
                      class="mt-2 space-y-3"
                    >
                      <div
                        v-for="(schedule, idx) in getScheduleList(activity)"
                        :key="`detail-sched-${activity.registrationID || activity.trainingID}-${schedule.id}`"
                        class="border border-gray-200 rounded-lg p-3 bg-gray-50"
                      >
                        <p class="text-sm font-semibold">
                          Session {{ idx + 1 }}
                        </p>
                        <p class="text-sm text-gray-700">
                          {{ formatScheduleRange(schedule) }}
                        </p>
                        <p class="text-xs text-gray-500" v-if="schedule.mode">
                          Mode: {{ schedule.mode }}
                        </p>
                        <p class="text-xs text-gray-500" v-if="schedule.location">
                          Location: {{ schedule.location }}
                        </p>
                        <p class="text-xs text-gray-500" v-if="schedule.trainingLink">
                          Link:
                          <a
                            :href="schedule.trainingLink"
                            target="_blank"
                            class="text-blue-500 underline"
                          >
                            {{ schedule.trainingLink }}
                          </a>
                        </p>
                      </div>
                    </div>

                    <div v-else class="mt-2 space-y-1 text-sm text-gray-600">
                      <p>Schedule not available yet.</p>
                      <p>
                        <strong>Mode:</strong> {{ getTrainingMode(activity) || "—" }}
                      </p>
                      <p v-if="getTrainingLocation(activity)">
                        <strong>Location:</strong> {{ getTrainingLocation(activity) }}
                      </p>
                      <p v-if="getTrainingLink(activity)">
                        <strong>Link:</strong>
                        <a
                          :href="getTrainingLink(activity)"
                          target="_blank"
                          class="text-blue-500 underline"
                        >
                          {{ getTrainingLink(activity) }}
                        </a>
                      </p>
                    </div>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-3">
                    <button
                      type="button"
                      class="px-3 py-1 rounded text-white"
                      :class="[
                        hasCertificate(activity)
                          ? 'bg-green-500 hover:bg-green-600'
                          : 'bg-gray-300 cursor-not-allowed',
                      ]"
                      :disabled="!hasCertificate(activity)"
                      @click.stop="downloadCertificate(activity, $event)"
                    >
                      {{
                        hasCertificate(activity)
                          ? "Download Certificate"
                          : "Certificate Unavailable"
                      }}
                    </button>
                  </div>
                </template>
              </div>
            </transition>
          </li>
        </ul>
      </div>
    </div>

    <!--Large screen-->
    <div class="min-h-screen px-3 pb-6 pt-3 font-poppins hidden lg:flex flex-col gap-4 max-w-6xl mx-auto">
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
                  v-for="step in currentSteps"
                  :key="`head-${step.key}`"
                  class="px-4 py-3 text-center text-sm font-medium text-gray-700 uppercase tracking-wider"
                >
                  {{ step.label }}
                </th>
                <th class="w-10"></th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <template
                v-for="activity in filteredActivities"
                :key="activity.title || activity.registrationID || activity.applicationID"
              >
                <!-- MAIN ROW -->
                <tr
                  class="hover:bg-gray-100 cursor-pointer"
                  @click="toggleRow(activity)"
                >
                  <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="font-semibold">
                      {{ activity.title }}
                    </div>
                    <div
                      v-if="activity.type === 'career'"
                      class="mt-1 text-xs text-gray-500 space-y-0.5"
                    >
                      <p>
                        <span class="font-semibold">Posting:</span>
                        {{ formatDateOnly(activity.postingDate) }}
                      </p>
                      <p>
                        <span class="font-semibold">Closing:</span>
                        {{ formatDateOnly(activity.closingDate) }}
                      </p>
                     
                    </div>
                    <div
                      v-else
                      class="mt-1 text-xs text-gray-500 space-y-0.5"
                    >
                      <p class="font-semibold">Schedule:</p>
                      <template v-if="getScheduleList(activity).length">
                        <div class="pl-3 space-y-1">
                          <p
                            v-for="schedule in getScheduleList(activity)"
                            :key="`table-sched-${activity.registrationID || activity.trainingID}-${schedule.id}`"
                          >
                            {{ formatScheduleDateRange(schedule) }}
                          </p>
                        </div>
                      </template>
                      <p v-else class="pl-3">
                        {{ formatDateOnly(getTrainingDate(activity)) }}
                      </p>
                    </div>
                  </td>

                  <td
                    v-for="(step, index) in currentSteps"
                    :key="`${activity.title}-${step.key}`"
                    class="px-4 py-4 text-sm text-gray-700 align-top text-center"
                  >
                    <div class="flex flex-col items-center gap-1">
                      <span
                        class="inline-flex w-4 h-4 rounded-full border-2"
                        :class="{
                          'border-blue-500 bg-blue-500': isStepActive(
                            activity,
                            step,
                            index
                          ),
                          'border-gray-300 bg-white': !isStepActive(
                            activity,
                            step,
                            index
                          ),
                        }"
                      ></span>
                      <span class="text-[11px] text-gray-500">
                        {{
                          getStepDate(activity, step)
                            ? formatDateOnly(getStepDate(activity, step))
                            : "—"
                        }}
                      </span>
                    </div>
                  </td>
                  <td class="px-2 py-4 text-right text-gray-400">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 inline"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </td>
                </tr>

                <!-- ▼ DROPDOWN DETAILS -->
                <tr v-if="expandedRow === activity" class="bg-gray-50">
                  <td :colspan="currentSteps.length + 2" class="px-6 py-4">
                    <!-- CAREER DETAILS -->
                    <div v-if="activity.type === 'career'">
                      <h3 class="text-lg font-bold mb-1">
                        {{ activity.title || activity.career?.position }}
                      </h3>

                      <p class="text-sm text-gray-600 mb-1">
                        <strong>Organization:</strong>
                        {{ activity.organizationName || activity.organization }}
                      </p>

                      <p v-if="activity.details || activity.career?.details" class="career-text-inline">
                        <strong>Details:</strong>
                        <span class="career-text-value">{{ activity.details || activity.career?.details }}</span>
                      </p>

                      <p>
                        <strong>Place of Assignment:</strong>
                        {{
                          activity.placeOfAssignment ||
                          activity.career?.placeOfAssignment ||
                          "Not specified"
                        }}
                      </p>

                      <p v-if="activity.qualificationStandard || activity.career?.qualificationStandard" class="career-text-inline">
                        <strong>Qualification Standard:</strong>
                        <span class="career-text-value">{{
                          activity.qualificationStandard ||
                          activity.career?.qualificationStandard
                        }}</span>
                      </p>

                      <p v-if="activity.pdf_directory">
                    
                        <a
                          :href="resolvePDFUrl(activity.pdf_directory)"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="text-blue-500 underline"
                        >
                          View Details
                        </a>
                      </p>

                      <p>
                        <strong>Posting Date:</strong>
                        {{
                          formatDateTime(activity.postingDate || activity.career?.postingDate)
                        }}
                      </p>

                      <p>
                        <strong>Closing Date:</strong>
                        {{
                          formatDateTime(activity.closingDate || activity.career?.closingDate)
                        }}
                      </p>

                      <div class="mt-4 flex flex-wrap gap-3">
                        <button
                          type="button"
                          :class="[
                            'px-3 py-1 rounded text-white',
                            activity.requirement_directory || activity.applicationID
                              ? 'bg-blue-500 hover:bg-blue-600'
                              : 'bg-gray-300 cursor-not-allowed',
                          ]"
                          @click.stop="downloadRequirement(activity, $event)"
                        >
                          View Requirement
                        </button>
                      </div>

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

                      <p>
                        <strong>Details:</strong> {{ activity.description }}
                      </p>

                      <div class="mt-3">
                        <p class="text-sm font-semibold text-gray-700">
                          Schedules
                        </p>

                        <div
                          v-if="getScheduleList(activity).length"
                          class="mt-2 grid gap-3 md:grid-cols-2"
                        >
                          <div
                            v-for="(schedule, idx) in getScheduleList(activity)"
                            :key="`desktop-detail-sched-${activity.registrationID || activity.trainingID}-${schedule.id}`"
                            class="border border-gray-200 rounded-lg p-3 bg-white shadow-inner"
                          >
                            <p class="text-sm font-semibold">
                              Session {{ idx + 1 }}
                            </p>
                            <p class="text-sm text-gray-700">
                              {{ formatScheduleRange(schedule) }}
                            </p>
                            <p class="text-xs text-gray-500" v-if="schedule.mode">
                              Mode: {{ schedule.mode }}
                            </p>
                            <p class="text-xs text-gray-500" v-if="schedule.location">
                              Location: {{ schedule.location }}
                            </p>
                            <p class="text-xs text-gray-500" v-if="schedule.trainingLink">
                              Link:
                              <a
                                :href="schedule.trainingLink"
                                target="_blank"
                                class="text-blue-500 underline"
                              >
                                {{ schedule.trainingLink }}
                              </a>
                            </p>
                          </div>
                        </div>

                        <div v-else class="mt-2 space-y-1 text-sm text-gray-600">
                          <p>Schedule not available yet.</p>
                          <p>
                            <strong>Mode:</strong>
                            {{ getTrainingMode(activity) || "—" }}
                          </p>
                          <p v-if="getTrainingLocation(activity)">
                            <strong>Location:</strong>
                            {{ getTrainingLocation(activity) }}
                          </p>
                          <p v-if="getTrainingLink(activity)">
                            <strong>Link:</strong>
                            <a
                              :href="getTrainingLink(activity)"
                              target="_blank"
                              class="text-blue-500 underline"
                            >
                              {{ getTrainingLink(activity) }}
                            </a>
                          </p>
                        </div>
                      </div>

                      <div class="mt-4 flex flex-wrap gap-3">
                        <button
                          type="button"
                          class="px-3 py-1 rounded text-white"
                          :class="[
                            hasCertificate(activity)
                              ? 'bg-green-500 hover:bg-green-600'
                              : 'bg-gray-300 cursor-not-allowed',
                          ]"
                          :disabled="!hasCertificate(activity)"
                          @click.stop="downloadCertificate(activity, $event)"
                        >
                          {{
                            hasCertificate(activity)
                              ? "Download Certificate"
                              : "Certificate Unavailable"
                          }}
                        </button>
                      </div>
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

/* Career text inline format to prevent overlap */
.career-text-inline {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 0.5rem;
  margin: 0.5rem 0;
  width: 100%;
}

.career-text-inline strong {
  flex-shrink: 0;
  white-space: nowrap;
}

.career-text-value {
  flex: 1;
  min-width: 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  line-height: 1.6;
}
</style>
