<script>
import { ref, onMounted } from "vue";
import axios from "axios";
import api from "@/api/axios";
import { uploadPDF, getPDFUrl } from "@/lib/supabase";
import { useToast } from "@/composables/useToast.js";

const pdfUrl = ref(null);
const showModal = ref(false);
const { toasts, showToast, showConfirmToast } = useToast();
// Helper to display readable status names
const displayStatus = (status) => {
  const map = {
    submitted: "Submitted",
    "for review": "For Review",
    "for interview": "For Interview",
    hired: "Hired",
    declined: "Declined",
  };
  return map[status] || status;
};

export default {
  data() {
    return {
      toasts: toasts,
      selectedCareer: { title: "", careerID: null, position: "" },
      newTagName: "",
      organizationLogo: null,
      globalSearchQuery: "",
      openUpcomingMenu: null,
      openCompletedMenu: null,

      showCareerDetailsModal: false,

      showViewScheduleModal: false,
      showScheduleModal: false,
      showStatusEmailModal: false,
      selectedPerson: null,
      isLoading : false,


      showAllUpcoming: false,
      showAllCompleted: false,

      isEditMode: false,
      careerToEditId: null,

      scheduleData: {
        date: "",
        mode: "",
        detail: "",
        cc: "",
        body: "",
      },
      scheduleAction: "scheduleOnly", // "scheduleOnly" or "scheduleAndEmail"
      showScheduleDropdown: false,
      statusEmailData: {
        body: "",
      },
      showConflictModal: false,
      conflictInfo: null,
      pendingSchedulePayload: null,
      pendingFormattedDate: "",
      sendingStatusEmail: false,

      applicantsList: [],
      applicantsLoading: false,
      applicantsError: "",
      applicantSearchQuery: "", // Search query for filtering applicants
      upcomingCareers: [],

      // Popup state + form
      showCareerPopup: false,
      newCareer: {
        position: "",
        placeOfAssignment: "",
        details: "",
        qualificationStandard: "",
        postingDate: "",
        closingDate: "",
        Tags: [],
        pdfPath: "",
      },
      tagOptions: [],
      careerPdfName: "",
      careerPdfPublicUrl: "",
      careerPdfUploading: false,
      careerPdfError: "",
      isOrganizationVerified: false,
    };
  },

  methods: {
    normalizeCareer(career = {}) {
      if (!career || typeof career !== "object") {
        return {};
      }

      const pdfPath =
        career.pdf_directory ??
        career.orgPdfPath ??
        career.org_pdf_directory ??
        career.pdfPath ??
        career.pdf_path ??
        career.pdf ??
        "";

      return {
        ...career,
        pdfPath: pdfPath || "",
        // Map to match actual database schema (camelCase)
        position: career.position ?? career.positionTitle ?? career.PositionTitle ?? "",
        placeOfAssignment: career.placeOfAssignment ?? career.PlaceOfAssignment ?? career.applicationLetterAddress ?? "",
        details: career.details ?? career.Details ?? career.detailsAndInstructions ?? "",
        qualificationStandard: career.qualificationStandard ?? career.qualificationStandards ?? career.QualificationStandards ?? career.qualifications ?? "",
        postingDate: career.postingDate ?? career.PostingDate ?? "",
        closingDate: career.closingDate ?? career.ClosingDate ?? career.deadlineOfSubmission ?? "",
        trainingsAttendedPercentage: career.trainingsAttendedPercentage ?? career.TrainingsAttendedPercentage ?? null,
      };
    },

    updateCareerPdfPreview(filePath) {
      if (!filePath) {
        this.careerPdfName = "";
        this.careerPdfPublicUrl = "";
        return;
      }
      this.careerPdfName = filePath.split("/").pop();
      this.careerPdfPublicUrl = getPDFUrl(filePath, "Requirements");
    },

    resetCareerPdfState() {
      this.careerPdfError = "";
      this.careerPdfName = "";
      this.careerPdfPublicUrl = "";
      this.careerPdfUploading = false;
      if (this.$refs?.careerPdfInput) {
        this.$refs.careerPdfInput.value = "";
      }
    },

    async handleCareerPdfUpload(event) {
      const file = event?.target?.files?.[0];
      if (!file) return;

      if (file.type !== "application/pdf") {
        showToast("Please upload a PDF file.", "error");
        event.target.value = "";
        return;
      }

      const MAX_BYTES = 10 * 1024 * 1024; // 10 MB
      if (file.size > MAX_BYTES) {
        showToast("PDF is too large. Maximum size is 10MB.", "error");
        event.target.value = "";
        return;
      }

      this.careerPdfUploading = true;
      this.careerPdfError = "";

      try {
        const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
        const orgId = storedUser?.organizationID || "anonymous-org";
        const folder = `org_pdf_directory/${orgId}`;

        const filePath = await uploadPDF(file, "Requirements", folder);
        if (!filePath) {
          throw new Error("Upload failed");
        }

        this.newCareer.pdfPath = filePath;
        this.updateCareerPdfPreview(filePath);
      } catch (error) {
        console.error("Error uploading career PDF:", error);
        this.careerPdfError =
          "Failed to upload PDF. Please try again or choose a different file.";
        this.newCareer.pdfPath = "";
        this.updateCareerPdfPreview("");
      } finally {
        this.careerPdfUploading = false;
      }
    },

    removeCareerPdf() {
      this.newCareer.pdfPath = "";
      this.updateCareerPdfPreview("");
      if (this.$refs?.careerPdfInput) {
        this.$refs.careerPdfInput.value = "";
      }
    },

    viewCareerPdf() {
      if (!this.newCareer.pdfPath) {
        showToast("No PDF attached yet.", "error");
        return;
      }
      const url =
        this.careerPdfPublicUrl ||
        getPDFUrl(this.newCareer.pdfPath, "Requirements");
      if (url) {
        window.open(url, "_blank");
      } else {
        showToast("Unable to open the PDF. Please try again.", "error");
      }
    },

    viewSelectedCareerPdf() {
      // Normalize in case the PDF path is stored under different field names
      const normalized = this.normalizeCareer(this.selectedCareer || {});
      const pdfPath =
        normalized.pdfPath ||
        normalized.orgPdfPath ||
        normalized.org_pdf_directory ||
        "";

      if (!pdfPath) {
        showToast("No PDF attached for this career.", "error");
        return;
      }

      // If org_pdf_directory already contains a full URL (Supabase public URL),
      // open it directly. Otherwise, build the URL via getPDFUrl.
      let url = "";
      if (typeof pdfPath === "string" && /^https?:\/\//i.test(pdfPath)) {
        url = pdfPath;
      } else {
        url = getPDFUrl(pdfPath, "Requirements");
      }
      if (url) {
        window.open(url, "_blank");
      } else {
        showToast("Unable to open the PDF. Please try again.", "error");
      }
    },

    async loadApplicantsForCareer(career) {
      const targetCareer = career || this.selectedCareer || null;
      if (!targetCareer) {
        console.warn("No career provided for applicants list.");
        return;
      }

      // Ensure token exists and trim it (remove stray quotes/spaces)
      let token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.");
        return;
      }
      token = token.trim().replace(/^"(.*)"$/, "$1");

      const careerID =
        targetCareer?.careerID ??
        targetCareer?.careerId ??
        targetCareer?.id ??
        targetCareer?._id ??
        null;

      if (!careerID) {
        console.error("Career object missing careerID:", targetCareer);
        return;
      }

      this.applicantsLoading = true;
      this.applicantsError = "";
      this.applicantsList = [];

      try {
        const response = await axios.get(
          import.meta.env.VITE_API_BASE_URL + `/careers/${careerID}/applicants`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );

        console.log("📋 Applicants API response:", response.data);
        console.log("📋 Applicants API response (full):", JSON.stringify(response.data, null, 2));
        console.log("📋 First applicant raw:", response.data?.[0]);
        console.log("📋 First applicant requirement_directory:", response.data?.[0]?.requirement_directory);

        // Check if requirement_directory exists in the raw response for any applicant
        if (Array.isArray(response.data)) {
          response.data.forEach((app, index) => {
            console.log(`📋 Applicant ${index} (ID: ${app.id || app.applicationID}):`, {
              hasRequirementDirectory: 'requirement_directory' in app,
              requirementDirectoryValue: app.requirement_directory,
              allKeys: Object.keys(app),
              fullObject: app
            });
          });
        }

        if (Array.isArray(response.data) && response.data.length > 0) {
          this.applicantsList = response.data.map((app) => {
            // Log the COMPLETE raw applicant object to see ALL available fields
            console.log("🔍 Raw applicant object (COMPLETE):", JSON.stringify(app, null, 2));
            console.log("🔍 Available fields:", Object.keys(app));
            console.log("🔍 Raw requirement_directory value:", app.requirement_directory);
            console.log("🔍 Raw requirementDirectory value:", app.requirementDirectory);
            console.log("🔍 Raw requirement_path value:", app.requirement_path);
            console.log("🔍 Raw requirements value:", app.requirements);
            console.log("🔍 Type of requirement_directory:", typeof app.requirement_directory);
            console.log("🔍 Is requirement_directory null?", app.requirement_directory === null);
            console.log("🔍 Is requirement_directory undefined?", app.requirement_directory === undefined);

            // Try to get requirement_directory from various possible locations
            let requirementDir = app.requirement_directory
              ?? app.requirementDirectory
              ?? app.requirement_path
              ?? app.requirementPath
              ?? null;

            // If requirements is a string (not an array), it might be the path
            if (!requirementDir && typeof app.requirements === "string" && app.requirements.trim().length > 0) {
              requirementDir = app.requirements;
            }

            // Check nested structures
            if (!requirementDir && app.application) {
              requirementDir = app.application.requirement_directory
                ?? app.application.requirementDirectory
                ?? app.application.requirement_path
                ?? null;
            }

            const mapped = {
              id: app.id ?? app.applicationID ?? app.applicationId ?? app._id,
              name:
                app.name ?? `${app.firstName || ""} ${app.lastName || ""}`.trim(),
              dateSubmitted: app.dateSubmitted ?? app.created_at ?? app.createdAt,
              status: app.status ? String(app.status).toLowerCase() : "submitted",
              requirement_directory: requirementDir,
              requirements: app.requirements ?? [],
              interviewSchedule: app.interviewSchedule ?? null,
              interviewMode: app.interviewMode ?? null,
              interviewLocation: app.interviewLocation ?? null,
              interviewLink: app.interviewLink ?? null,
            };

            // Debug log for requirement_directory
            console.log(`📋 Applicant ${mapped.id} (${mapped.name}) requirement_directory:`, mapped.requirement_directory);
            console.log(`📋 Applicant ${mapped.id} full mapped object:`, mapped);

            // If requirement_directory is still null, log a warning
            if (!mapped.requirement_directory) {
              console.warn(`⚠️ WARNING: requirement_directory is NULL for applicant ${mapped.id} even though database has value!`);
              console.warn(`⚠️ This suggests the backend API is not returning the field correctly.`);
            }

            return mapped;
          });
        } else {
          this.applicantsList = [];
        }
      } catch (error) {
        console.error("Error fetching applicants:", error);
        console.error("Error response:", error.response);
        
        // Helper function to sanitize error messages (remove technical PHP errors)
        const sanitizeErrorMessage = (message) => {
          if (!message || typeof message !== 'string') return null;
          // Filter out technical PHP errors and show user-friendly messages
          if (message.includes('Call to a member function') || 
              message.includes('on string') ||
              message.includes('Fatal error') ||
              message.includes('Parse error') ||
              message.includes('Warning:') ||
              message.includes('Notice:')) {
            return null; // Don't show technical errors
          }
          return message;
        };

        if (error.response?.status === 401) {
          this.applicantsError = "Token invalid or expired. Please log in again.";
          showToast("Unauthorized. Please log in again.", "error");
        } else if (error.response?.status === 403) {
          this.applicantsError = "Access denied. You don't have permission to view applicants for this career.";
          showToast(this.applicantsError, "error");
        } else if (error.response?.status === 500) {
          // For 500 errors, always show a user-friendly message, not the raw backend error
          const backendMessage = sanitizeErrorMessage(error.response?.data?.message);
          this.applicantsError = backendMessage || "Server error occurred while fetching applicants. Please try again later.";
          showToast("Server error: Unable to load applicants. Please try again later.", "error");
        } else {
          // For other errors, sanitize the message if it exists
          const backendMessage = sanitizeErrorMessage(error.response?.data?.message);
          this.applicantsError = backendMessage || "An error occurred while fetching applicants. Please try again.";
          showToast(this.applicantsError, "error");
        }
        this.applicantsList = [];
      } finally {
        this.applicantsLoading = false;
      }
    },

    async updateApplicationStatus(person, event) {
      const token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.");
        return;
      }

      // Get the index first to access the original status
      const index = this.applicantsList.findIndex((a) => a.id === person.id);
      
      // Store original status BEFORE the change (from the list, not from person object which may already be updated)
      const originalStatus = index !== -1 ? this.applicantsList[index].status : null;
      const newStatus = person.status; // This is the new status from the dropdown

      console.log(
        "Updating status for application ID:",
        person.id,
        "Original status:",
        originalStatus,
        "New status:",
        newStatus
      );

      try {
        const response = await axios.put(
          import.meta.env.VITE_API_BASE_URL +
            `/applications/${person.id}/status`,
          { status: newStatus },
          {
            headers: {
              Authorization: `Bearer ${token.trim()}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        // Update local state with response data
        if (index !== -1) {
          const updatedData = response.data?.data || response.data || {};
          const normalizedStatus = updatedData.applicationStatus
            ? String(updatedData.applicationStatus).toLowerCase()
            : String(newStatus).toLowerCase();

          Object.assign(this.applicantsList[index], {
            status: normalizedStatus,
          });

          if (Object.prototype.hasOwnProperty.call(updatedData, "interviewSchedule")) {
            this.applicantsList[index].interviewSchedule = updatedData.interviewSchedule;
          }
          if (Object.prototype.hasOwnProperty.call(updatedData, "interviewMode")) {
            this.applicantsList[index].interviewMode = updatedData.interviewMode;
          }
          if (Object.prototype.hasOwnProperty.call(updatedData, "interviewLocation")) {
            this.applicantsList[index].interviewLocation = updatedData.interviewLocation;
          }
          if (Object.prototype.hasOwnProperty.call(updatedData, "interviewLink")) {
            this.applicantsList[index].interviewLink = updatedData.interviewLink;
          }

          // Open scheduling modal only after successful status update to "for interview"
          if (normalizedStatus === "for interview" && !this.applicantsList[index].interviewSchedule) {
            this.$nextTick(() => {
              this.openScheduleModal(this.applicantsList[index]);
            });
          }

          // Open status email modal automatically after successful status update to "hired", "declined", or "rejected"
          const normalizedStatusTrimmed = normalizedStatus.trim();
          
          console.log("Status change check:", {
            normalizedStatus: normalizedStatusTrimmed,
            newStatus: newStatus,
            originalStatus: originalStatus,
            shouldOpen: normalizedStatusTrimmed === "hired" || normalizedStatusTrimmed === "declined" || normalizedStatusTrimmed === "rejected"
          });
          
          // Check both normalizedStatus and newStatus to catch any case variations
          const statusToCheck = normalizedStatusTrimmed || String(newStatus).toLowerCase().trim();
          if (statusToCheck === "hired" || statusToCheck === "declined" || statusToCheck === "rejected") {
            console.log("Opening status email modal for:", statusToCheck);
            // Use setTimeout to ensure the DOM has updated and any other modals are closed
            setTimeout(() => {
              console.log("Calling openStatusEmailModal with:", this.applicantsList[index]);
              if (this.applicantsList[index]) {
                this.openStatusEmailModal(this.applicantsList[index]);
              } else {
                console.error("Applicant not found in list at index:", index);
              }
            }, 300);
          } else {
            console.log("Modal not opening - status is:", statusToCheck);
          }
        }
      } catch (error) {
        console.error("Error updating status:", error);
        console.error("Error response:", error.response);
        // Revert to original status on error
        person.status = originalStatus;
        if (error.response?.status === 401) {
          showToast(
            "Unauthorized. Please log in again. Error: " +
              (error.response.data?.message || "Token invalid or expired")
          );
        } else if (error.response?.status === 403) {
          showToast(
            "Access denied. You don't have permission to update this application status."
          );
        } else {
          showToast(
            error.response?.data?.message ||
              "Failed to update application status. Please try again."
          );
        }
      }
    },

    openStatusEmailModal(person) {
      console.log("openStatusEmailModal called with person:", person);
      this.selectedPerson = person;
      this.statusEmailData.body = "";
      this.showStatusEmailModal = true;
      console.log("showStatusEmailModal set to:", this.showStatusEmailModal);
    },

    closeStatusEmailModal() {
      this.showStatusEmailModal = false;
      this.selectedPerson = null;
      this.statusEmailData.body = "";
      this.sendingStatusEmail = false;
    },

    async sendStatusEmail() {
      if (!this.selectedPerson) {
        showToast("No applicant selected.", "error");
        return;
      }

      const token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.", "error");
        return;
      }

      // Set loading state
      this.sendingStatusEmail = true;

      try {
        const payload = {};
        if (this.statusEmailData.body && this.statusEmailData.body.trim()) {
          payload.body = this.statusEmailData.body.trim();
        }

        const response = await axios.post(
          import.meta.env.VITE_API_BASE_URL +
            `/applications/${this.selectedPerson.id}/send-status-email`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token.trim()}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        showToast(response.data?.message || "Email sent successfully!", "success");
        this.closeStatusEmailModal();
      } catch (error) {
        console.error("Error sending status email:", error);
        if (error.response?.status === 401) {
          showToast("Unauthorized. Please log in again.", "error");
        } else if (error.response?.status === 403) {
          showToast("Access denied. You don't have permission to send this email.", "error");
        } else if (error.response?.status === 400) {
          showToast(error.response?.data?.message || "Cannot send email for this status.", "error");
        } else {
          showToast(
            error.response?.data?.message ||
              "Failed to send email. Please try again.",
            "error"
          );
        }
      } finally {
        this.sendingStatusEmail = false;
      }
    },

    async viewRequirements(applicationID, rawFilePath) {
      let token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to view requirements.", "error");
        return;
      }

      token = token.trim().replace(/^"(.*)"$/, "$1");

      if (!applicationID) {
        showToast("Application ID missing. Please refresh the page.", "error");
        return;
      }

      const normalizedAppID = String(applicationID);
      let filePath = typeof rawFilePath === "string" && rawFilePath.trim() ? rawFilePath.trim() : null;

      console.log("👁️ View Requirements - applicationID:", applicationID, "rawFilePath:", rawFilePath);

      // First, try to get file path from already-loaded applicants list
      if (!filePath && Array.isArray(this.applicantsList) && this.applicantsList.length > 0) {
        const applicant = this.applicantsList.find((app) => {
          const candidates = [
            app.id,
            app.applicationID,
            app.applicationId,
            app._id,
          ]
            .filter(Boolean)
            .map((val) => String(val));
          return candidates.includes(normalizedAppID);
        });

        if (applicant) {
          console.log("👁️ Found applicant in list:", applicant);
          filePath = applicant?.requirement_directory || null;
          console.log("👁️ Extracted filePath from applicant list:", filePath);
        }
      }

      // If still not found, try refreshing the applicants list to get latest data
      if (!filePath && this.selectedCareer?.careerID) {
        try {
          console.log("👁️ Refreshing applicants list to get latest data...");
          const response = await axios.get(
            `${import.meta.env.VITE_API_BASE_URL}/careers/${this.selectedCareer.careerID}/applicants`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
              },
            }
          );

          if (Array.isArray(response.data)) {
            const applicant = response.data.find(
              (app) => String(app.id ?? app.applicationID ?? app.applicationId ?? app._id ?? "") === normalizedAppID
            );

            if (applicant) {
              console.log("👁️ Found applicant in fresh API response:", applicant);
              console.log("👁️ All applicant keys:", Object.keys(applicant));

              // Try all possible field names
              filePath = applicant?.requirement_directory
                ?? applicant?.requirementDirectory
                ?? applicant?.requirement_path
                ?? applicant?.requirementPath
                ?? (typeof applicant?.requirements === "string" ? applicant.requirements : null)
                ?? null;

              // Check nested application object
              if (!filePath && applicant?.application) {
                filePath = applicant.application?.requirement_directory
                  ?? applicant.application?.requirementDirectory
                  ?? applicant.application?.requirement_path
                  ?? null;
              }

              console.log("👁️ Extracted filePath from fresh API response:", filePath);

              // Update the applicants list with the fresh data
              const index = this.applicantsList.findIndex(a => String(a.id) === normalizedAppID);
              if (index !== -1 && filePath) {
                this.applicantsList[index].requirement_directory = filePath;
                console.log("✅ Updated applicants list with filePath");
              }
            }
          }
        } catch (error) {
          console.warn("⚠️ Error refreshing applicants list:", error);
        }
      }

      const openRequirementUrl = (url) => {
        const win = window.open(url, "_blank", "noopener,noreferrer");
        if (!win) {
          showToast("Please allow pop-ups to view the requirements.");
        }
      };

      // If we have a file path, try to get PDF URL from Supabase
      if (filePath && typeof filePath === "string" && filePath.trim().length > 0) {
        console.log("👁️ Attempting to get PDF URL for path:", filePath.trim());
        const pdfUrl = getPDFUrl(filePath.trim(), "Requirements");
        console.log("👁️ Generated PDF URL:", pdfUrl);

        if (pdfUrl) {
          openRequirementUrl(pdfUrl);
          console.log("✅ Opening requirements in new tab:", pdfUrl);
          return;
        } else {
          console.warn("⚠️ Could not generate PDF URL from file path");
        }
      }

      // If no file path found, show helpful error message
      console.error("❌ No requirement file path found for application:", applicationID);
      console.error("❌ This likely means the backend API is not returning the 'requirement_directory' field");
      console.error("❌ Please check that the /careers/{careerID}/applicants endpoint includes requirement_directory in its response");

      showToast("Unable to view requirements. The requirement file path is not available in the API response. Please contact support or check if the file was uploaded correctly.", "error");
    },

    async downloadRequirements(applicationID, rawFilePath) {
      let token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to download requirements.", "error");
        return;
      }

      // Trim token to remove any extra spaces or quotes
      token = token.trim().replace(/^"(.*)"$/, "$1");

      if (!applicationID) {
        showToast("Application ID missing. Please refresh the page.", "error");
        return;
      }

      const normalizedAppID = String(applicationID);
      let filePath =
        typeof rawFilePath === "string" && rawFilePath.trim() ? rawFilePath.trim() : null;

      /**
       * Helper: try to resolve the requirement path from the already loaded applicants list.
       */
      const resolvePathFromApplicants = () => {
        if (!Array.isArray(this.applicantsList) || this.applicantsList.length === 0) {
          console.log("🔍 resolvePathFromApplicants: applicantsList is empty or not an array");
          return null;
        }

        console.log("🔍 resolvePathFromApplicants: Searching for applicationID:", normalizedAppID);
        console.log("🔍 resolvePathFromApplicants: applicantsList length:", this.applicantsList.length);

        const applicant = this.applicantsList.find((app) => {
          const candidates = [
            app.id,
            app.applicationID,
            app.applicationId,
            app._id,
          ]
            .filter(Boolean)
            .map((val) => String(val));
          return candidates.includes(normalizedAppID);
        });

        if (!applicant) {
          console.log("🔍 resolvePathFromApplicants: No applicant found with ID:", normalizedAppID);
          return null;
        }

        console.log("🔍 resolvePathFromApplicants: Found applicant:", applicant);

        // Check requirement_directory first (this should be a string path)
        let path = applicant?.requirement_directory
          ?? applicant?.requirementDirectory
          ?? applicant?.requirement_path
          ?? null;

        // If requirements is an array, don't use it - we need a string path
        // Only use requirements if it's a string
        if (!path && applicant?.requirements) {
          if (typeof applicant.requirements === "string") {
            path = applicant.requirements;
          } else {
            console.warn("🔍 resolvePathFromApplicants: requirements is not a string, ignoring:", applicant.requirements);
          }
        }

        console.log("🔍 resolvePathFromApplicants: Extracted path:", path, "Type:", typeof path);

        // Ensure we return a string or null, not an object or array
        if (path && typeof path === "string" && path.trim().length > 0) {
          return path.trim();
        }

        if (path) {
          console.warn("🔍 resolvePathFromApplicants: Path exists but is not a valid string:", path, typeof path);
        }

        return null;
      };

      /**
       * Helper: hit the public /applications API to fetch the requirement path.
       * This mirrors the logic that previously worked for the applicant portal.
       */
      const resolvePathFromApi = async () => {
        try {
          // Try the careers endpoint first - it might have the requirement_directory
          // Since we're already in a career context, try getting the applicant from the career's applicants endpoint
          if (this.selectedCareer?.careerID) {
            try {
              const careerApplicantsResponse = await axios.get(
                `${import.meta.env.VITE_API_BASE_URL}/careers/${this.selectedCareer.careerID}/applicants`,
                {
                  headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                  },
                }
              );

              if (Array.isArray(careerApplicantsResponse.data)) {
                const applicant = careerApplicantsResponse.data.find(
                  (app) => String(app.id ?? app.applicationID ?? app.applicationId ?? app._id ?? "") === normalizedAppID
                );

                if (applicant) {
                  console.log("🔍 Found applicant in career applicants endpoint:", applicant);
                  const path = applicant?.requirement_directory
                    ?? applicant?.requirementDirectory
                    ?? applicant?.requirement_path
                    ?? (typeof applicant?.requirements === "string" ? applicant.requirements : null)
                    ?? null;

                  if (path && typeof path === "string") {
                    console.log("✅ Found requirement path from career applicants endpoint:", path);
                    return path.trim();
                  }
                }
              }
            } catch (careerError) {
              console.warn("Career applicants endpoint failed:", careerError);
            }
          }

          // Prefer a scoped endpoint if available, otherwise fall back to the full list.
          const detailEndpoint = `${import.meta.env.VITE_API_BASE_URL}/applications/${applicationID}`;
          let response;
          try {
            response = await axios.get(detailEndpoint, {
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
                "Content-Type": "application/json",
              },
            });
            console.log("🔍 API detail response:", response.data);

            // Check multiple possible field names and nested structures
            const path = response?.data?.requirement_directory
              ?? response?.data?.data?.requirement_directory
              ?? response?.data?.requirementDirectory
              ?? response?.data?.requirement_path
              ?? response?.data?.data?.requirementDirectory
              ?? (typeof response?.data?.requirements === "string" ? response.data.requirements : null)
              ?? null;

            if (path && typeof path === "string") {
              console.log("✅ Found requirement path from detail endpoint:", path);
              return path.trim();
            }
          } catch (detailError) {
            // The detail endpoint might not exist or might return 500; fall back to listing.
            console.warn("Applications detail endpoint failed:", detailError.response?.status, detailError.message);
          }

          const listResponse = await axios.get(
            `${import.meta.env.VITE_API_BASE_URL}/applications`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
                "Content-Type": "application/json",
              },
            }
          );
          if (Array.isArray(listResponse.data)) {
            const match = listResponse.data.find(
              (app) =>
                String(app.applicationID ?? app.id ?? app._id ?? "") === normalizedAppID
            );
            if (match) {
              console.log("🔍 Found match in applications list:", match);
              const path = match?.requirement_directory
                ?? match?.requirementDirectory
                ?? match?.requirement_path
                ?? match?.requirements
                ?? null;
              if (path) {
                console.log("✅ Found requirement path from list:", path);
                return path;
              }
            }
          }
        } catch (apiError) {
          console.error("Unable to fetch applications for requirement path:", apiError);
        }
        return null;
      };

      // Try to resolve file path
      console.log("🔍 Starting downloadRequirements for applicationID:", applicationID);
      console.log("🔍 Initial rawFilePath:", rawFilePath);

      if (!filePath) {
        filePath = resolvePathFromApplicants();
        console.log("🔍 After resolvePathFromApplicants:", filePath);
      }
      if (!filePath) {
        filePath = await resolvePathFromApi();
        console.log("🔍 After resolvePathFromApi:", filePath);
      }

      // Validate filePath is a non-empty string before proceeding
      if (filePath && typeof filePath !== "string") {
        console.warn("Invalid filePath type:", typeof filePath, filePath);
        filePath = String(filePath); // Try to convert to string
      }

      const hasValidFilePath = filePath && typeof filePath === "string" && filePath.trim().length > 0;
      console.log("🔍 Final filePath:", filePath);
      console.log("🔍 hasValidFilePath:", hasValidFilePath);

      // Try Supabase first if we have a valid file path
      if (hasValidFilePath) {
        try {
          const trimmedPath = filePath.trim();
          console.log("📥 Attempting Supabase download with path:", trimmedPath);

          const pdfUrl = getPDFUrl(trimmedPath, "Requirements");

          if (!pdfUrl) {
            console.warn("❌ Could not generate PDF URL from file path:", trimmedPath);
            // Fall through to backend attempt
          } else {
            console.log("🔗 Generated Supabase URL:", pdfUrl);
            const response = await fetch(pdfUrl);

            console.log("📡 Supabase fetch response status:", response.status, response.statusText);

            if (response.ok) {
              const blob = await response.blob();
              console.log("📦 Blob size:", blob.size, "bytes");

              if (blob.size > 0) {
                const url = window.URL.createObjectURL(blob);
                const fileName = trimmedPath.split("/").pop() || `requirement_${applicationID}.pdf`;

                const a = document.createElement("a");
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                console.log("✅ Requirements downloaded successfully from Supabase");
                return;
              } else {
                console.warn("⚠️ Downloaded file from Supabase is empty, trying backend...");
              }
            } else {
              console.warn(`⚠️ Supabase download failed with status ${response.status}, trying backend...`);
              // Log response text for debugging
              try {
                const text = await response.text();
                console.warn("Supabase error response:", text);
              } catch (e) {
                // Ignore if we can't read response
              }
            }
          }
        } catch (supabaseError) {
          console.error("❌ Supabase download error:", supabaseError);
          // Fall through to backend attempt
        }
      } else {
        console.log("⚠️ No valid file path found, trying backend endpoint...");
      }

      // Try backend endpoint (either as fallback or primary if no filePath)
      try {
        console.log("Attempting to download from backend endpoint...");
        const response = await axios({
          url: `${import.meta.env.VITE_API_BASE_URL}/applications/${applicationID}/requirement`,
          method: "GET",
          responseType: "blob",
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/pdf",
          },
        });

        if (response.data && response.data.size > 0) {
          const url = window.URL.createObjectURL(
            new Blob([response.data], { type: "application/pdf" })
          );
          const a = document.createElement("a");
          a.href = url;
          a.download = `requirement_${applicationID}.pdf`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);

          console.log("✅ Requirements downloaded successfully from backend");
          return;
        } else {
          throw new Error("Downloaded file is empty");
        }
      } catch (backendError) {
        console.error("Backend requirement download failed:", backendError);

        // Only show "No requirement file" if we've confirmed there's no filePath
        if (backendError.response?.status === 404) {
          if (!hasValidFilePath) {
            showToast("No requirement file has been uploaded for this application.", "error");
          } else {
            showToast("Requirement file not found. The file may have been deleted or moved.", "error");
          }
        } else if (backendError.response?.status === 401) {
          showToast("Unauthorized. Please log in again.", "error");
        } else if (backendError.response?.status === 403) {
          showToast("Access denied. You don't have permission to download this requirement.", "error");
        } else {
          const errorMsg = backendError.response?.data?.message || backendError.message || "Please try again.";
          showToast(`Failed to download requirements: ${errorMsg}`, "error");
        }
      }
    },

    async confirmSchedule() {
      if (
        !this.scheduleData.date ||
        !this.scheduleData.mode ||
        !this.scheduleData.detail
      ) {
        showToast("Please fill out all fields.");
        return;
      }

      const token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.");
        return;
      }

      if (!this.selectedPerson || !this.selectedPerson.id) {
        showToast("No applicant selected.");
        return;
      }

      try {
        const dateObj = new Date(this.scheduleData.date);
        if (isNaN(dateObj.getTime())) {
          showToast("Invalid date selected. Please try again.");
          return;
        }

        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, "0");
        const day = String(dateObj.getDate()).padStart(2, "0");
        const hours = String(dateObj.getHours()).padStart(2, "0");
        const minutes = String(dateObj.getMinutes()).padStart(2, "0");
        const seconds = String(dateObj.getSeconds()).padStart(2, "0");
        const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

        const interviewMode =
          this.scheduleData.mode === "onSite" ? "On-Site" : "Online";

        const payload = {
          interviewSchedule: formattedDate,
          interviewMode,
          interviewLocation:
            this.scheduleData.mode === "onSite"
              ? this.scheduleData.detail
              : null,
          interviewLink:
            this.scheduleData.mode === "online"
              ? this.scheduleData.detail
              : null,
          // Only include email fields if "Schedule and Email" is selected
          sendEmail: this.scheduleAction === "scheduleAndEmail",
          cc: this.scheduleAction === "scheduleAndEmail" ? (this.scheduleData.cc || null) : null,
          body: this.scheduleAction === "scheduleAndEmail" ? (this.scheduleData.body || null) : null,
        };

        const conflict = this.findScheduleConflict(formattedDate);
        if (conflict) {
          this.pendingSchedulePayload = payload;
          this.pendingFormattedDate = formattedDate;
          this.conflictInfo = conflict;
          this.showConflictModal = true;
          return;
        }

        await this.submitInterviewSchedule(payload, formattedDate);
      } catch (error) {
        this.handleScheduleError(error);
      }
    },

    toTimestamp(dateString) {
      if (!dateString) return null;
      const normalized = dateString.includes("T")
        ? dateString
        : dateString.replace(" ", "T");
      const date = new Date(normalized);
      return Number.isNaN(date.getTime()) ? null : date.getTime();
    },

    findScheduleConflict(formattedDate) {
      const targetTs = this.toTimestamp(formattedDate);
      if (!targetTs) return null;

      // Default interview duration: 30 minutes (can be adjusted)
      const INTERVIEW_DURATION_MS = 30 * 60 * 1000; // 30 minutes in milliseconds
      const newInterviewStart = targetTs;
      const newInterviewEnd = targetTs + INTERVIEW_DURATION_MS;

      const conflict = this.applicantsList.find((app) => {
        if (
          !app.interviewSchedule ||
          app.id === this.selectedPerson?.id ||
          !app.interviewSchedule.trim()
        ) {
          return false;
        }
        const existingTs = this.toTimestamp(app.interviewSchedule);
        if (!existingTs) return false;

        // Calculate existing interview time range
        const existingInterviewStart = existingTs;
        const existingInterviewEnd = existingTs + INTERVIEW_DURATION_MS;

        // Check for overlap: new interview overlaps if it starts before existing ends AND ends after existing starts
        const hasOverlap = 
          (newInterviewStart < existingInterviewEnd && newInterviewEnd > existingInterviewStart);

        return hasOverlap;
      });

      if (!conflict) return null;

      return {
        name: conflict.name || "another applicant",
        interviewSchedule: conflict.interviewSchedule,
      };
    },

    async submitInterviewSchedule(payload, formattedDate) {
      const token = localStorage.getItem("token");
      const response = await axios.put(
        import.meta.env.VITE_API_BASE_URL +
          `/applications/${this.selectedPerson.id}/interview`,
        payload,
        {
          headers: {
            Authorization: `Bearer ${token.trim()}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      if (!(response.data.success || response.data.message)) {
        throw new Error("Unexpected response format");
      }

      const index = this.applicantsList.findIndex(
        (a) => a.id === this.selectedPerson.id
      );
      if (index !== -1) {
        const updatedData = response.data.data || response.data;
        const updatedApplicant = {
          ...this.applicantsList[index],
          status: "for interview",
          interviewSchedule: updatedData.interviewSchedule || formattedDate,
          interviewMode:
            updatedData.interviewMode ||
            (this.scheduleData.mode === "onSite" ? "On-Site" : "Online"),
          interviewLocation:
            updatedData.interviewLocation ||
            (this.scheduleData.mode === "onSite"
              ? this.scheduleData.detail
              : null),
          interviewLink:
            updatedData.interviewLink ||
            (this.scheduleData.mode === "online"
              ? this.scheduleData.detail
              : null),
        };

        if (this.$set) {
          this.$set(this.applicantsList, index, updatedApplicant);
        } else {
          this.applicantsList[index] = updatedApplicant;
          this.applicantsList = [...this.applicantsList];
        }
      }

      this.closeScheduleModal();
      
      // Show appropriate message based on email sending status
      if (response.data.email_sent) {
        showToast(
          "✅ Interview scheduled and email sent successfully!"
        );
      } else if (response.data.email_error) {
        showToast(
          "⚠️ Interview scheduled, but email could not be sent: " + response.data.email_error,
          "error"
        );
      } else {
        showToast(
          "✅ Interview scheduled successfully! The schedule has been saved."
        );
      }
    },

    handleScheduleError(error) {
      console.error("Error scheduling interview:", error);
      console.error("Error response:", error?.response);
      const status = error?.response?.status;
      if (status === 401) {
        showToast(
          "Unauthorized. Please log in again. Error: " +
            (error.response.data?.message || "Token invalid or expired")
        );
      } else if (status === 403) {
        showToast(
          "Access denied. You don't have permission to schedule interviews for this application."
        );
      } else if (status === 422) {
        showToast(
          "Validation error: " +
            (error.response.data?.message ||
              "Please check your input and try again.")
        );
      } else {
        showToast(
          error.response?.data?.message ||
            "Failed to schedule interview. Please try again."
        );
      }
    },

    confirmConflictSchedule() {
      if (!this.pendingSchedulePayload || !this.pendingFormattedDate) {
        this.cancelConflictSchedule();
        return;
      }
      this.showConflictModal = false;
      const payload = this.pendingSchedulePayload;
      const formattedDate = this.pendingFormattedDate;
      this.pendingSchedulePayload = null;
      this.pendingFormattedDate = "";
      this.submitInterviewSchedule(payload, formattedDate).catch((error) =>
        this.handleScheduleError(error)
      );
    },

    cancelConflictSchedule() {
      this.showConflictModal = false;
      this.conflictInfo = null;
      this.pendingSchedulePayload = null;
      this.pendingFormattedDate = "";
    },

    checkToken() {
      const token = localStorage.getItem("token");
      if (!token) {
        showToast("🔒 Please log in to continue.");
        return false; // Token is not valid
      }
      return token; // Token is valid
    },

    async addTag() {
      const trimmedTagName = this.newTagName.trim();
      if (!trimmedTagName) {
        showToast("Please enter a tag name.");
        return;
      }

      const token = this.checkToken(); // Check token before proceeding
      if (!token) return; // If token is invalid, exit

      // Check if tag already exists (case-insensitive)
      const existingTag = this.tagOptions.find(
        (tag) => tag.TagName.toLowerCase() === trimmedTagName.toLowerCase()
      );

      if (existingTag) {
        // Tag exists, just select it if not already selected
        const normalizedId = Number(existingTag.TagID);
        if (!this.newCareer.Tags.includes(normalizedId)) {
          this.newCareer.Tags.push(normalizedId);
        }
        // Clear the input field
        this.newTagName = "";
        return;
      }

      // Tag doesn't exist, create it
      try {
        // Send the new tag to the backend
        const response = await axios.post(
          import.meta.env.VITE_API_BASE_URL + "/tags",
          {
            TagName: trimmedTagName,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
            },
          }
        );

        // Add the new tag to the tagOptions and newCareer.Tags
        this.tagOptions.push(response.data);
        this.newCareer.Tags.push(Number(response.data.TagID));

        // Clear the input field
        this.newTagName = "";
      } catch (error) {
        console.error("Error adding tag:", error);
        showToast("Failed to add tag.");
      }
    },

    // Toggle selection of a tag
    toggleTag(tagID) {
      if (!Array.isArray(this.newCareer.Tags)) {
        this.newCareer.Tags = [];
      }
      const normalizedId = Number(tagID);
      if (this.newCareer.Tags.includes(normalizedId)) {
        this.newCareer.Tags = this.newCareer.Tags.filter(
          (id) => id !== normalizedId
        );
      } else {
        this.newCareer.Tags.push(normalizedId);
      }
    },

    // Check if a tag is selected
    isTagSelected(tagID) {
      const normalizedId = Number(tagID);
      return (
        Array.isArray(this.newCareer.Tags) &&
        this.newCareer.Tags.includes(normalizedId)
      );
    },

    // Remove a tag directly from the selected tags
    removeTag(tagID) {
      if (!Array.isArray(this.newCareer.Tags)) return; // 👈 prevents crashes

      const normalizedId = Number(tagID);
      const index = this.newCareer.Tags.indexOf(normalizedId);
      if (index !== -1) {
        this.newCareer.Tags.splice(index, 1);
      }
    },

    async fetchTags() {
      try {
        const response = await axios.get(
          import.meta.env.VITE_API_BASE_URL + "/tags"
        );
        this.tagOptions = response.data; // Update tagOptions correctly
        console.log(this.tagOptions); // Log the tags to see if they are fetched correctly
      } catch (error) {
        console.error("Error fetching tags:", error);
      }
    },

    getTagName(id) {
      const normalizedId = Number(id);
      const tag = this.tagOptions.find((t) => Number(t.TagID) === normalizedId);
      return tag ? tag.TagName : "";
    },

    toggleUpcomingMenu(id) {
      this.openUpcomingMenu = this.openUpcomingMenu === id ? null : id;
      this.openCompletedMenu = null;
    },
    toggleCompletedMenu(id) {
      this.openCompletedMenu = this.openCompletedMenu === id ? null : id;
      this.openUpcomingMenu = null;
    },
    handleClickOutside(event) {
      const clickedInside = event.target.closest(".menu-icon, .dropdown-menu");
      if (!clickedInside) {
        this.openUpcomingMenu = null;
        this.openCompletedMenu = null;
      }
      // Close schedule dropdown if clicking outside
      const clickedInsideScheduleDropdown = event.target.closest(".schedule-dropdown-wrapper");
      if (!clickedInsideScheduleDropdown) {
        this.showScheduleDropdown = false;
      }
    },

    showMoreUpcoming() {
      this.visibleUpcomingCount += 4; // show 4 more
    },
    showMoreCompleted() {
      this.visibleCompletedCount += 4;
    },

    // ✅ OPEN SCHEDULE MODAL (only when status = For Review)
    openScheduleModal(person) {
      if (person.status === "for interview") {
        this.selectedPerson = person;
        this.showScheduleModal = true;
        this.showViewScheduleModal = false;
        this.scheduleAction = "scheduleOnly";
        this.showScheduleDropdown = false;

        // If person already has a schedule, populate the form with existing data
        if (person.interviewSchedule) {
          // Convert the interview schedule to datetime-local format (YYYY-MM-DDTHH:mm)
          const scheduleDate = new Date(person.interviewSchedule);
          if (!isNaN(scheduleDate.getTime())) {
            const year = scheduleDate.getFullYear();
            const month = String(scheduleDate.getMonth() + 1).padStart(2, "0");
            const day = String(scheduleDate.getDate()).padStart(2, "0");
            const hours = String(scheduleDate.getHours()).padStart(2, "0");
            const minutes = String(scheduleDate.getMinutes()).padStart(2, "0");
            this.scheduleData.date = `${year}-${month}-${day}T${hours}:${minutes}`;
          } else {
            this.scheduleData.date = "";
          }

          // Set mode based on interviewMode
          if (person.interviewMode === "On-Site") {
            this.scheduleData.mode = "onSite";
            this.scheduleData.detail = person.interviewLocation || "";
          } else if (person.interviewMode === "Online") {
            this.scheduleData.mode = "online";
            this.scheduleData.detail = person.interviewLink || "";
          } else {
            this.scheduleData.mode = "";
            this.scheduleData.detail = "";
          }

          this.scheduleData.cc = person.emailCc || "";
          this.scheduleData.body = person.emailBody || "";
        } else {
          // Reset schedule data for new schedule
          this.scheduleData = {
            date: "",
            mode: "",
            detail: "",
            cc: "",
            body: "",
          };
        }
      }
    },
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.scheduleData = { date: "", mode: "", detail: "", cc: "", body: "" };
      this.scheduleAction = "scheduleOnly";
      this.showScheduleDropdown = false;
      this.selectedPerson = null;
      this.cancelConflictSchedule();
    },

    selectScheduleAction(action) {
      this.scheduleAction = action;
      this.showScheduleDropdown = false;
    },

    getScheduleButtonText() {
      if (this.selectedPerson?.interviewSchedule) {
        return this.scheduleAction === "scheduleAndEmail" 
          ? "Update Schedule and Email" 
          : "Update Schedule";
      }
      return this.scheduleAction === "scheduleAndEmail" 
        ? "Schedule and Email" 
        : "Schedule only";
    },

    handleScheduleDropdownBlur(event) {
      // Delay closing to allow click events to fire first
      setTimeout(() => {
        // Check if focus moved to dropdown menu or if clicking outside
        const relatedTarget = event.relatedTarget;
        if (!relatedTarget || !relatedTarget.closest('.schedule-dropdown-wrapper')) {
          this.showScheduleDropdown = false;
        }
      }, 150);
    },

    // Delete a career
    async deleteCareer(career) {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before performing this action.");
        return;
      }

      const careerId = career.careerID || career.id; // fallback to id
      if (!careerId) {
        console.error("No career ID provided", career);
        return;
      }

      const confirmDelete = await this.showConfirmToast(
        `Are you sure you want to delete "${career.position || 'this career'}"?`
      );
      if (!confirmDelete) return;

      try {
        const token = localStorage.getItem("token");
        await axios.delete(
          import.meta.env.VITE_API_BASE_URL + `/careers/${careerId}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        // Refresh from backend so all sections (open/closed) stay in sync
        await this.fetchCareers();

        showToast("✅ Career deleted successfully!");
      } catch (error) {
        console.error("ERROR DELETING CAREER:", error.response?.data || error);
        showToast("❌ Failed to delete career. See console for details.");
      }
    },

    // Open career in edit mode
    async updateCareer(career) {
      // Use openCareerPopup to ensure consistency
      await this.openCareerPopup(career);
    },

    // Save career (create or update) - DUPLICATE METHOD - DEPRECATED, keeping for backward compatibility
    // This method appears to be unused in favor of the one below

    resetNewCareer() {
      this.newCareer = {
        position: "",
        placeOfAssignment: "",
        details: "",
        qualificationStandard: "",
        postingDate: "",
        closingDate: "",
        Tags: [],
        pdfPath: "",
      };
      this.isEditMode = false;
      this.careerToEditId = null;
      this.resetCareerPdfState();
    },

    // ✅ OPEN VIEW SCHEDULE MODAL (only when status = Interview Scheduled)
    openViewScheduleModal(person) {
      if (person.status === "for interview" && person.interviewSchedule) {
        this.selectedPerson = person;
        this.showViewScheduleModal = true;
        this.showScheduleModal = false;
      }
    },
    closeViewScheduleModal() {
      this.showViewScheduleModal = false;
      this.selectedPerson = null;
    },

    formatInterviewDateTime(dateTimeString) {
      if (!dateTimeString) return "Not scheduled";
      try {
        // Validate input type
        if (typeof dateTimeString !== 'string' && typeof dateTimeString !== 'number') {
          return "Invalid date";
        }
        
        const date = new Date(dateTimeString);
        if (isNaN(date.getTime())) {
          // Try parsing as MySQL datetime format (YYYY-MM-DD HH:mm:ss)
          if (typeof dateTimeString === 'string') {
            const parts = dateTimeString.split(" ");
            if (parts.length === 2) {
              const [datePart, timePart] = parts;
              const [year, month, day] = datePart.split("-");
              const [hour, minute] = timePart.split(":");
              if (year && month && day && hour && minute) {
                const parsedDate = new Date(year, month - 1, day, hour, minute);
                if (!isNaN(parsedDate.getTime())) {
                  return parsedDate.toLocaleString("en-US", {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  });
                }
              }
            }
          }
          return dateTimeString || "Invalid date";
        }
        return date.toLocaleString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      } catch (error) {
        console.warn("Error formatting interview date:", error, dateTimeString);
        return dateTimeString || "Not scheduled";
      }
    },
    openCalendar() {
      const input = this.$refs.dateInput;
      if (!input) {
        console.warn("dateInput ref not found");
        return;
      }

      // Try the modern showPicker() first (works in some browsers)
      try {
        if (typeof input.showPicker === "function") {
          input.showPicker();
          return;
        }
      } catch (err) {
        console.debug("showPicker() failed:", err);
      }

      // Next try focusing then clicking (some browsers open picker on click/focus)
      try {
        input.focus();
        // Clicking the input sometimes triggers the native UI
        input.click();
        return;
      } catch (err) {
        console.debug("input.click()/focus failed:", err);
      }

      // Final fallback: open a small helper so user can still pick — alert as last resort
      console.warn(
        "Native picker was not opened programmatically by the browser. Consider adding a JS datepicker as a reliable fallback."
      );
    },

    async fetchCareers() {
      this.isLoading = true; // start loading
      try {
        const response = await api.get("/organization/careers");
        const newCareers = Array.isArray(response.data) ? response.data : [];

        // Rebuild the local list from fresh API data to avoid stale or duplicate entries
        this.upcomingCareers = [];

        newCareers.forEach((career) => {
          const normalizedCareer = this.normalizeCareer(career);
          const existingIndex = this.upcomingCareers.findIndex(
            (c) => c.careerID === normalizedCareer.careerID
          );

          if (existingIndex > -1) {
            this.upcomingCareers[existingIndex] = {
              ...this.upcomingCareers[existingIndex],
              ...normalizedCareer,
            };
          } else {
            this.upcomingCareers.push(normalizedCareer);
          }
        });
      } catch (error) {
        console.error("ERROR FETCHING CAREERS:", error);
      } finally {
      this.isLoading = false; // stop loading
      }
    },
    formatDateForInput(dateString) {
      if (!dateString) return "";
      
      try {
        // Handle different date formats from API
        let date;
        if (typeof dateString === 'string') {
          // If it's already in YYYY-MM-DD format, return as is
          if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
            return dateString;
          }
          // If it includes time, extract just the date part
          if (dateString.includes('T') || dateString.includes(' ')) {
            date = new Date(dateString);
          } else {
            date = new Date(dateString);
          }
        } else {
          date = new Date(dateString);
        }
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
          return "";
        }
        
        // Format as YYYY-MM-DD for date input
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
      } catch (error) {
        console.error("Error formatting date:", error);
        return "";
      }
    },
    async openCareerPopup(career = null) {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before creating or editing careers.");
        return;
      }

      await this.fetchTags(); // Load tags first
      
      if (career) {
        const normalizedCareer = this.normalizeCareer(career);
        // Editing existing career - populate ALL fields
        this.newCareer = {
          position: normalizedCareer.position || "",
          placeOfAssignment: normalizedCareer.placeOfAssignment || "",
          details: normalizedCareer.details || "",
          qualificationStandard: normalizedCareer.qualificationStandard || "",
          postingDate: this.formatDateForInput(normalizedCareer.postingDate) || "",
          closingDate: this.formatDateForInput(normalizedCareer.closingDate) || "",
          Tags: Array.isArray(normalizedCareer.Tags)
            ? normalizedCareer.Tags.map(
              (tag) => Number(tag.TagID ?? tag.tagID ?? tag.id)
            )
            : [],
          pdfPath: normalizedCareer.pdfPath || "",
        };
        this.isEditMode = true; // editing
        this.careerToEditId =
          normalizedCareer.careerID || normalizedCareer.id || null;
        this.updateCareerPdfPreview(this.newCareer.pdfPath);
        this.careerPdfError = "";
      } else {
        // Posting new career
        this.resetNewCareer();
        // Set posting date to today's date automatically
        this.newCareer.postingDate = this.todayDate;
      }
      if (!Array.isArray(this.newCareer.Tags)) {
        this.newCareer.Tags = [];
      }
      this.showCareerPopup = true;
    },
    closeCareerPopup() {
      this.showCareerPopup = false;
      this.resetNewCareer();
    },
    openCareerDetails(career) {
      try {
        if (!career) {
          showToast("Invalid career data. Please try again.", "error");
          return;
        }
        const normalizedCareer = this.normalizeCareer(career);
        this.selectedCareer = {
          ...normalizedCareer,
          careerID: normalizedCareer.careerID, // keep the correct ID
          _id: normalizedCareer.careerID, // mirror for safety
        };
        this.applicantsList = [];
        this.applicantsError = "";
        this.applicantsLoading = false;
        this.applicantSearchQuery = ""; // Clear search when opening modal
        this.showCareerDetailsModal = true;
        // Load applicants after modal is shown to prevent blocking
        this.$nextTick(() => {
          this.loadApplicantsForCareer(normalizedCareer);
        });
      } catch (error) {
        console.error("Error opening career details:", error);
        showToast("Error opening career details. Please try again.", "error");
      }
    },
    closeCareerDetails() {
      this.showCareerDetailsModal = false;
      this.applicantSearchQuery = ""; // Clear search when closing modal
    },
    async saveCareer() {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before performing this action.");
        return;
      }

      try {
        // 🔹 0. Get token from localStorage
        let token = localStorage.getItem("token");
        console.log("🔹 Raw token read from localStorage:", token);
        if (!token) {
          showToast("🔒 Please log in to continue.");
          return;
        }

        // Remove extra spaces or surrounding quotes
        token = token.trim().replace(/^"(.*)"$/, "$1");
        console.log("🔹 Using token:", token);

        // 🔹 1. Validate required fields
        const requiredFields = [
          "position",
          "placeOfAssignment",
          "postingDate",
          "closingDate",
        ];

        for (const field of requiredFields) {
          if (!this.newCareer[field]) {
            showToast(`Please fill out the field: ${field}`);
            return;
          }
        }

        if (this.careerPdfUploading) {
          showToast("Please wait for the PDF upload to finish before saving.", "error");
          return;
        }

        const pdfPath = this.newCareer.pdfPath || "";

        // 🔹 1.5. Validate details and qualificationStandard - required only if no PDF is uploaded
        if (!pdfPath || pdfPath.trim() === "") {
          // No PDF uploaded, so details and qualificationStandard are required
          if (!this.newCareer.details || this.newCareer.details.trim() === "") {
            showToast("Please fill out the details field or upload a PDF file.");
            return;
          }
          if (!this.newCareer.qualificationStandard || this.newCareer.qualificationStandard.trim() === "") {
            showToast("Please fill out the qualification standard field or upload a PDF file.");
            return;
          }
        }
        // If PDF is uploaded, details and qualificationStandard are optional

        // 🔹 2. Optional tags confirmation
        if (!this.newCareer.Tags || this.newCareer.Tags.length === 0) {
          const proceed = confirm("No tags selected. Continue without tags?");
          if (!proceed) return;
        }

        // 🔹 3. Prepare payload (matches actual database schema - camelCase)
        const payload = {
          position: this.newCareer.position,
          placeOfAssignment: this.newCareer.placeOfAssignment,
          details: this.newCareer.details,
          qualificationStandard: this.newCareer.qualificationStandard,
          pdf_directory: pdfPath || null,
          postingDate: this.newCareer.postingDate,
          closingDate: this.newCareer.closingDate,
          Tags: this.newCareer.Tags || [],
        };
        console.log("🔹 Payload:", payload);

        // 🔹 4. Send POST request
        const url =
          this.isEditMode && this.careerToEditId
            ? import.meta.env.VITE_API_BASE_URL +
              `/careers/${this.careerToEditId}`
            : import.meta.env.VITE_API_BASE_URL + "/careers";
        const method = this.isEditMode && this.careerToEditId ? "put" : "post";

        const response = await axios({
          method,
          url,
          data: payload,
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        });

        // 🔹 5. Handle success
        if (response?.status >= 200 && response?.status < 300) {
          console.log("✅ Career saved:", response.data);
          showToast(
            this.isEditMode
              ? "Career updated successfully!"
              : "Career posted successfully!"
          );
          if (!this.isEditMode) {
            const savedCareer = this.normalizeCareer(
              response.data.data || response.data
            );
            this.upcomingCareers.push(savedCareer);
          }
          this.closeCareerPopup();
          this.resetNewCareer();
          await this.fetchCareers();
        }
      } catch (error) {
        console.error("❌ ERROR SAVING CAREER:", error);

        if (error.response) {
          console.log("🔹 Server response:", error.response.data);
          if (error.response.status === 401) {
            showToast(
              "Unauthorized: Your token is invalid or expired. Please log in again."
            );
            localStorage.removeItem("token"); // Optional: force logout
          } else if (error.response.status === 422) {
            showToast("Validation failed. Check your inputs.");
          } else if (error.response.status === 409) {
            showToast("This career already exists!");
          } else {
            showToast(
              "Server error: " +
                (error.response.data.message || "Please try again.")
            );
          }
        } else if (error.request) {
          showToast("No response from server. Check your network or server.");
        } else {
          showToast("Error: " + error.message);
        }
      }
    },
    resetNewCareer() {
      this.newCareer = {
        position: "",
        placeOfAssignment: "",
        details: "",
        qualificationStandard: "",
        postingDate: "",
        closingDate: "",
        trainingsAttendedPercentage: null,
        Tags: [],
        pdfPath: "",
      };
      this.resetCareerPdfState();
    },

    formatdeadline(deadline) {
      if (!deadline) return "No deadline set";
      try {
        // Handle string dates that might be in different formats
        if (typeof deadline !== 'string' && typeof deadline !== 'number') {
          return "Invalid date";
        }
        const date = new Date(deadline);
        if (isNaN(date.getTime())) {
          return deadline; // Return original if invalid
        }
        return date.toLocaleString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        });
      } catch (error) {
        console.warn("Error formatting deadline:", error, deadline);
        return deadline || "No deadline set";
      }
    },
    formatDate(date) {
      if (!date) return "Not set";
      try {
        // Handle string dates that might be in different formats
        if (typeof date !== 'string' && typeof date !== 'number') {
          return "Invalid date";
        }
        const dateObj = new Date(date);
        if (isNaN(dateObj.getTime())) {
          return date || "Not set"; // Return original if invalid
        }
        return dateObj.toLocaleString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        });
      } catch (error) {
        console.warn("Error formatting date:", error, date);
        return date || "Not set";
      }
    },
  },

  computed: {
    logoUrl() {
      if (this.organizationLogo) {
        const logoPath = this.organizationLogo.logo_directory || 
                        this.organizationLogo.Logo_directory || 
                        this.organizationLogo.logoPath;
        if (logoPath) {
          const url = getImageUrl(logoPath, "Requirements");
          return url || null;
        }
      }
      return null;
    },
    todayDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0");
      const day = String(today.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`; // YYYY-MM-DD
    },
    visibleUpcomingCareers() {
      const list = this.sortedUpcomingCareers;
      return this.showAllUpcoming ? list : list.slice(0, 4);
    },
    visibleCompletedCareers() {
      const list = this.sortedCompletedCareers;
      return this.showAllCompleted ? list : list.slice(0, 4);
    },
    sortedUpcomingCareers() {
      const now = new Date();
      const list = Array.isArray(this.upcomingCareers)
        ? this.upcomingCareers
        : [];
      return list
        .filter((c) => {
          const closingDate = c.closingDate || c.deadlineOfSubmission;
          if (!closingDate) return true;
          const d = new Date(closingDate);
          return d >= now; // only future closing dates
        })
        .sort(
          (a, b) => {
            const aDate = a.closingDate || a.deadlineOfSubmission;
            const bDate = b.closingDate || b.deadlineOfSubmission;
            if (!aDate || !bDate) return 0;
            return new Date(aDate) - new Date(bDate);
          }
        );
    },

    sortedCompletedCareers() {
      const now = new Date();
      const list = Array.isArray(this.upcomingCareers)
        ? this.upcomingCareers
        : [];
      return list
        .filter((c) => {
          const closingDate = c.closingDate || c.deadlineOfSubmission;
          if (!closingDate) return false;
          const d = new Date(closingDate);
          return d < now; // only past closing dates
        })
        .sort(
          (a, b) => {
            const aDate = a.closingDate || a.deadlineOfSubmission;
            const bDate = b.closingDate || b.deadlineOfSubmission;
            if (!aDate || !bDate) return 0;
            return new Date(bDate) - new Date(aDate);
          }
        );
    },
    filteredUpcomingCareers() {
      const query = this.globalSearchQuery.toLowerCase();
      return this.sortedUpcomingCareers.filter((career) =>
        (career.position || "").toLowerCase().includes(query)
      );
    },
    // 🔹 Filter upcoming careers (real-time, from start of string)
    filteredUpcomingCareers() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedUpcomingCareers;
      return this.sortedUpcomingCareers.filter((career) =>
        (career.position || "").toLowerCase().startsWith(query)
      );
    },

    // 🔹 Filter completed careers (real-time, from start of string)
    filteredCompletedCareers() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedCompletedCareers;
      return this.sortedCompletedCareers.filter((career) =>
        (career.position || "").toLowerCase().startsWith(query)
      );
    },

    // 🔹 Respect “View More / View Less” logic
    visibleFilteredUpcoming() {
      return this.showAllUpcoming
        ? this.filteredUpcomingCareers
        : this.filteredUpcomingCareers.slice(0, 4);
    },

    visibleFilteredCompleted() {
      return this.showAllCompleted
        ? this.filteredCompletedCareers
        : this.filteredCompletedCareers.slice(0, 4);
    },
    // Filter tags based on search input
    filteredTags() {
      if (!this.newTagName || this.newTagName.trim() === "") {
        return this.tagOptions;
      }
      const searchQuery = this.newTagName.toLowerCase().trim();
      return this.tagOptions.filter((tag) =>
        tag.TagName.toLowerCase().includes(searchQuery)
      );
    },
    // Filter applicants based on search query
    filteredApplicants() {
      if (!this.applicantSearchQuery || this.applicantSearchQuery.trim() === "") {
        return this.applicantsList;
      }
      const query = this.applicantSearchQuery.toLowerCase().trim();
      return this.applicantsList.filter(applicant => {
        const name = (applicant.name || "").toLowerCase();
        const id = String(applicant.id || "").toLowerCase();
        const status = (applicant.status || "").toLowerCase();
        return name.includes(query) || id.includes(query) || status.includes(query);
      });
    },
    selectedCareerPdfName() {
      if (!this.selectedCareer) return "";
      const normalized = this.normalizeCareer(this.selectedCareer);
      const path =
        normalized.pdfPath ||
        normalized.orgPdfPath ||
        normalized.org_pdf_directory ||
        "";
      if (!path) return "";
      return String(path).split("/").pop();
    },
  },

  mounted() {
    // Get organization logo from localStorage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser);
        if (user.organization) {
          this.organizationLogo = user.organization;
        } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
          this.organizationLogo = user;
        }
        
        // Initialize organization verification status
        // Check nested organization status first, then fall back to user status
        let organizationStatus = null;
        if (user.organization) {
          organizationStatus = user.organization.status || user.organization.Status || null;
        }
        if (!organizationStatus) {
          organizationStatus = user.status || user.Status || null;
        }
        
        this.isOrganizationVerified = ["approved", "verified"].includes(
          (organizationStatus || "").toString().toLowerCase()
        );
      } catch (error) {
        console.error("Error parsing user from localStorage:", error);
      }
    }
    
    this.fetchCareers();
    this.fetchTags();
    document.addEventListener("click", this.handleClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
  },
};
</script>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useOrganizationLogo } from "@/composables/useOrganizationLogo.js";

const router = useRouter();
const isSidebarOpen = ref(true);
const organizationName = ref("");
const organizationStatus = ref(null);

// Get organization logo
const { logoUrl } = useOrganizationLogo();

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Get org name from localStorage on mount
onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    if (user.role === "organization") {
      organizationName.value = user.displayName || user.name;
      // Check nested organization status first, then fall back to user status
      if (user.organization) {
        organizationStatus.value = user.organization.status || user.organization.Status || null;
      }
      if (!organizationStatus.value) {
        organizationStatus.value = user.status || user.Status || null;
      }
    }
  }
});

// Computed properties for verification status
const organizationStatusLabel = computed(() => {
  const isVerified = ["approved", "verified"].includes(
    (organizationStatus.value || "").toString().toLowerCase()
  );
  return isVerified ? "Verified" : "Unverified";
});

const organizationStatusClass = computed(() => {
  const isVerified = ["approved", "verified"].includes(
    (organizationStatus.value || "").toString().toLowerCase()
  );
  return isVerified ? "org-status-verified" : "org-status-unverified";
});

// Sidebar navigation functions
const goToProfile = () => router.push("/profile");
const goToHome = () => router.push("/organization");
const goToTrainings = () => router.push({ name: "OrgTrainings" });
const goToCareers = () => router.push({ name: "OrgCareers" });
const goToCalendar = () => router.push({ name: "OrgCalendar" });

// Generic navigation function
const navigateTo = (route) => {
  router.push(route);
};

const logout = () => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Login" });
};

async function viewRequirement(id) {
  const response = await axios.get(`${API_BASE_URL}/requirements/${id}`, {
    responseType: "blob",
  });

  const blob = new Blob([response.data], { type: "application/pdf" });
  pdfUrl.value = URL.createObjectURL(blob);
}
</script>

<template>
  <div class="organization-careers">
    <!-- Hamburger Toggle -->
    <button
      class="hamburger"
      @click="toggleSidebar"
      :class="{ open: isSidebarOpen, shifted: isSidebarOpen }"
    >
      <span></span>
      <span></span>
      <span></span>
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside class="sidebar" :class="{ collapsed: !isSidebarOpen }">
        <div class="space"></div>
        <!-- Avatar always visible -->
        <div class="avatar">
          <img v-if="logoUrl" :src="logoUrl" alt="Organization Logo" class="avatar-img" />
          <div v-else class="avatar-placeholder">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M20.59 22C20.59 18.13 16.74 15 12 15C7.26 15 3.41 18.13 3.41 22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>

        <!-- Profile Section (only when sidebar is open) -->
        <transition name="fade">
          <div v-if="isSidebarOpen" class="profile-section">
            <h3 class="org-name">
              {{ organizationName }}
              <span v-if="organizationStatus !== null" class="org-status-inline" :class="organizationStatusClass">
                <svg v-if="isOrganizationVerified" class="org-status-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                  <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg v-else class="org-status-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                  <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>{{ organizationStatusLabel }}</span>
              </span>
            </h3>
            <div class="profile-actions">
              <div class="action" @click="navigateTo({ name: 'OrgUpdateProfile' })">
                <!-- Update Profile Icon -->
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M11.7278 8.27191C12.7534 9.87525 14.1247 11.2375 15.7464 12.2534L8.85673 19.144C8.43166 19.569 8.21841 19.7816 7.95731 19.9213C7.69637 20.0609 7.40171 20.1199 6.81278 20.2377L3.73563 20.853C3.40302 20.9195 3.23649 20.9525 3.14188 20.8578C3.04759 20.7632 3.08035 20.5971 3.14677 20.2651L3.76298 17.1879C3.88087 16.5985 3.93965 16.3035 4.07938 16.0424C4.21912 15.7814 4.43173 15.569 4.85673 15.144L11.7278 8.27191ZM16.1116 4.03656C16.6711 3.75929 17.3284 3.75931 17.888 4.03656C18.1821 4.18229 18.455 4.45518 19.0003 5.00043C19.5453 5.54545 19.8184 5.81774 19.9641 6.11175C20.2414 6.67123 20.2413 7.32861 19.9641 7.88812C19.8184 8.18221 19.5455 8.45517 19.0003 9.00043L17.2034 10.7963C15.5308 9.84498 14.1456 8.46859 13.1819 6.81781L15.0003 5.00043C15.5453 4.45539 15.8176 4.18234 16.1116 4.03656Z"
                    fill="#FFFDFD"
                  />
                </svg>
                <span>Update Profile</span>
              </div>
              <div class="action" @click="navigateTo({ name: 'OrgChangePassword' })">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                    stroke="#FFFDFD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path
                    d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.258 9.77251 19.9887C9.5799 19.7194 9.31074 19.5143 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.742 9.96512 4.01129 9.77251C4.28059 9.5799 4.48571 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15Z"
                    stroke="#FFFDFD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Change Password</span>
              </div>
            </div>
          </div>
        </transition>

        <div class="icon" @click="navigateTo('/organization')">
          <svg
            width="30"
            height="30"
            viewBox="0 0 30 30"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M6.25 17.0585C6.25 16.0494 6.25 15.5448 6.47166 15.1141C6.69333 14.6833 7.1039 14.3901 7.92505 13.8035L13.8375 9.58034C14.3989 9.17938 14.6795 8.9789 15 8.9789C15.3205 8.9789 15.6011 9.17938 16.1625 9.58034L22.075 13.8035C22.8961 14.3901 23.3067 14.6833 23.5283 15.1141C23.75 15.5448 23.75 16.0494 23.75 17.0585V24.25C23.75 25.1928 23.75 25.6642 23.4571 25.9571C23.1642 26.25 22.6928 26.25 21.75 26.25H8.25C7.30719 26.25 6.83579 26.25 6.54289 25.9571C6.25 25.6642 6.25 25.1928 6.25 24.25V17.0585Z"
              fill="white"
            />
            <path
              d="M3.75 15.6366C3.75 15.9035 3.75 16.0369 3.8341 16.0781C3.91819 16.1192 4.02352 16.0373 4.23418 15.8734L13.7721 8.45502C14.362 7.99625 14.6569 7.76686 15 7.76686C15.3431 7.76686 15.638 7.99625 16.2279 8.45502L25.7658 15.8734C25.9765 16.0373 26.0818 16.1192 26.1659 16.0781C26.25 16.0369 26.25 15.9035 26.25 15.6366V14.7282C26.25 14.2478 26.25 14.0076 26.1483 13.7997C26.0466 13.5918 25.857 13.4444 25.4779 13.1495L16.2279 5.95502C15.638 5.49625 15.3431 5.26686 15 5.26686C14.6569 5.26686 14.362 5.49625 13.7721 5.95502L4.52212 13.1495C4.14295 13.4444 3.95337 13.5918 3.85168 13.7997C3.75 14.0076 3.75 14.2478 3.75 14.7282V15.6366Z"
              fill="white"
            />
            <path
              d="M16.125 18.75H13.875C12.7704 18.75 11.875 19.6454 11.875 20.75V26.1C11.875 26.1828 11.9422 26.25 12.025 26.25H17.975C18.0578 26.25 18.125 26.1828 18.125 26.1V20.75C18.125 19.6454 17.2296 18.75 16.125 18.75Z"
              fill="white"
            />
            <rect
              x="20"
              y="6.25"
              width="2.5"
              height="5"
              rx="0.5"
              fill="white"
            />
          </svg>
          <span>Home</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgTrainings' })">
          <svg
            width="29"
            height="29"
            viewBox="0 0 29 29"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M20.5837 3.625C23.4119 3.625 24.8261 3.62526 25.7048 4.50391C26.5833 5.3826 26.5837 6.79675 26.5837 9.625V19.375C26.5837 22.2033 26.5833 23.6174 25.7048 24.4961C24.8261 25.3747 23.4119 25.375 20.5837 25.375H8.41666C5.58824 25.375 4.17425 25.3748 3.29557 24.4961C2.41689 23.6174 2.41666 22.2034 2.41666 19.375V9.625C2.41666 6.79657 2.41689 5.38259 3.29557 4.50391C4.17425 3.62523 5.58824 3.625 8.41666 3.625H20.5837ZM9.66666 12.292C9.11438 12.292 8.66666 12.7397 8.66666 13.292V20.542L8.67155 20.6445C8.72303 21.1485 9.1491 21.542 9.66666 21.542C10.1842 21.542 10.6103 21.1485 10.6618 20.6445L10.6667 20.542V13.292C10.6667 12.7397 10.2189 12.292 9.66666 12.292ZM19.3337 9.875C18.7814 9.875 18.3337 10.3227 18.3337 10.875V20.542L18.3385 20.6436C18.3896 21.148 18.8158 21.542 19.3337 21.542C19.8514 21.5418 20.2778 21.1479 20.3288 20.6436L20.3337 20.542V10.875C20.3337 10.3228 19.8858 9.87518 19.3337 9.875ZM14.4997 14.708C13.9476 14.7082 13.4998 15.156 13.4997 15.708V20.541L13.5046 20.6436C13.5557 21.1477 13.982 21.5408 14.4997 21.541C15.0175 21.541 15.4436 21.1478 15.4948 20.6436L15.4997 20.541V15.708C15.4995 15.1559 15.0518 14.708 14.4997 14.708Z"
              fill="white"
            />
          </svg>
          <span>Trainings</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgCareers' })">
          <svg
            width="25"
            height="25"
            viewBox="0 0 25 25"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M22.8798 11.0484C23.4046 10.8642 23.9845 11.1431 24.1081 11.6855C24.4792 13.3134 24.4217 15.0018 23.9268 16.6191C23.4739 18.0989 22.6698 19.4685 21.5787 20.6449C21.2148 21.0372 20.6017 21.0197 20.2239 20.6408L14.9487 15.3495C14.4292 14.8284 14.6314 13.9436 15.3257 13.6999L22.8798 11.0484ZM13 4.0826C13 3.50231 13.4932 3.04057 14.0672 3.12592C15.8633 3.39302 17.5788 4.00579 19.085 4.93161C20.3794 5.72731 21.4793 6.72976 22.3343 7.87824C22.709 8.38157 22.4513 9.07932 21.8592 9.28718L14.3313 11.9301C13.6809 12.1584 13 11.6758 13 10.9865V4.0826Z"
              fill="white"
            />
            <path
              d="M11.9511 13.2908C11.9512 13.5763 12.0581 13.8518 12.25 14.0632L19.2677 21.7886C19.6714 22.233 19.5963 22.9337 19.0759 23.2331C18.245 23.7112 17.354 24.0924 16.4209 24.365C14.5402 24.9144 12.5476 25.0087 10.6201 24.6394C8.69236 24.2701 6.88846 23.4478 5.36911 22.2468C3.84993 21.0459 2.66126 19.5025 1.90915 17.7537C1.15711 16.0048 0.864996 14.1043 1.05759 12.2205C1.25024 10.3365 1.92171 8.52693 3.01364 6.95288C4.1056 5.37884 5.58398 4.08845 7.31735 3.19605C8.43484 2.62073 9.63633 2.22318 10.8757 2.01305C11.4515 1.91541 11.9511 2.37876 11.9511 2.96284V13.2908Z"
              fill="white"
            />
          </svg>
          <span>Career</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgCalendar' })">
          <svg
            width="26"
            height="26"
            viewBox="0 0 26 26"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M2.16675 9.4165C2.16675 7.53089 2.16675 6.58808 2.75253 6.00229C3.33832 5.4165 4.28113 5.4165 6.16675 5.4165H19.8334C21.719 5.4165 22.6618 5.4165 23.2476 6.00229C23.8334 6.58808 23.8334 7.53089 23.8334 9.4165V9.83317C23.8334 10.3046 23.8334 10.5403 23.687 10.6867C23.5405 10.8332 23.3048 10.8332 22.8334 10.8332H3.16675C2.69534 10.8332 2.45964 10.8332 2.31319 10.6867C2.16675 10.5403 2.16675 10.3046 2.16675 9.83317V9.4165Z"
              fill="white"
            />
            <path
              d="M22.833 13C23.3042 13 23.5401 13.0002 23.6865 13.1465C23.833 13.2929 23.833 13.5286 23.833 14V19.833C23.833 21.7186 23.8329 22.6613 23.2471 23.2471C22.6613 23.8329 21.7186 23.833 19.833 23.833H6.16699C4.28137 23.833 3.33872 23.8329 2.75293 23.2471C2.16714 22.6613 2.16699 21.7186 2.16699 19.833V14C2.16699 13.5286 2.16703 13.2929 2.31348 13.1465C2.45994 13.0002 2.69576 13 3.16699 13H22.833ZM8.58301 19.5C8.11182 19.5 7.87591 19.5001 7.72949 19.6465C7.58321 19.7929 7.58301 20.0288 7.58301 20.5V20.667C7.58301 21.1382 7.58308 21.3741 7.72949 21.5205C7.87591 21.6669 8.11182 21.667 8.58301 21.667H10.917C11.3882 21.667 11.6241 21.6669 11.7705 21.5205C11.9169 21.3741 11.917 21.1382 11.917 20.667V20.5C11.917 20.0288 11.9168 19.7929 11.7705 19.6465C11.6241 19.5001 11.3882 19.5 10.917 19.5H8.58301ZM15.083 19.5C14.6118 19.5 14.3759 19.5001 14.2295 19.6465C14.0832 19.7929 14.083 20.0288 14.083 20.5V20.667C14.083 21.1382 14.0831 21.3741 14.2295 21.5205C14.3759 21.6669 14.6118 21.667 15.083 21.667H17.417C17.8882 21.667 18.1241 21.6669 18.2705 21.5205C18.4169 21.3741 18.417 21.1382 18.417 20.667V20.5C18.417 20.0288 18.4168 19.7929 18.2705 19.6465C18.1241 19.5001 17.8882 19.5 17.417 19.5H15.083ZM8.58301 15.167C8.11182 15.167 7.87591 15.1671 7.72949 15.3135C7.58337 15.4599 7.58301 15.6959 7.58301 16.167V16.333C7.58301 16.8041 7.58337 17.0401 7.72949 17.1865C7.87591 17.3329 8.11182 17.333 8.58301 17.333H10.917C11.3882 17.333 11.6241 17.3329 11.7705 17.1865C11.9166 17.0401 11.917 16.8041 11.917 16.333V16.167C11.917 15.6959 11.9166 15.4599 11.7705 15.3135C11.6241 15.1671 11.3882 15.167 10.917 15.167H8.58301ZM15.083 15.167C14.6118 15.167 14.3759 15.1671 14.2295 15.3135C14.0834 15.4599 14.083 15.6959 14.083 16.167V16.333C14.083 16.8041 14.0834 17.0401 14.2295 17.1865C14.3759 17.3329 14.6118 17.333 15.083 17.333H17.417C17.8882 17.333 18.1241 17.3329 18.2705 17.1865C18.4166 17.0401 18.417 16.8041 18.417 16.333V16.167C18.417 15.6959 18.4166 15.4599 18.2705 15.3135C18.1241 15.1671 17.8882 15.167 17.417 15.167H15.083Z"
              fill="white"
            />
            <path
              d="M7.58325 3.25L7.58325 6.5"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
            <path
              d="M18.4167 3.25L18.4167 6.5"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
          <span>Calendar</span>
        </div>

        <div class="spacer"></div>
        <!-- pushes signout down -->
        <div class="icon signout" @click="logout">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 24 24"
            fill="none"
            stroke="white"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sign Out</span>
        </div>
      </aside>
    </transition>

    <!-- Main content -->
    <main class="content">
      <header class="topbar">
        <div class="Pathfinder-wrapper">
          <span class="logo-text">Pathfinder</span>
        </div>
      </header>

      <div>
        <!-- 🔥 Global Loader -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-16">
          <svg
            class="animate-spin h-12 w-12 text-blue-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v8H4z"
            ></path>
          </svg>
        </div>

        <!-- ✅ Search & Careers Sections -->
        <div v-else>
          <!-- ✅ GLOBAL SEARCH -->
          <section class="global-search-section">
            <div class="flex justify-center my-6 px-4">
              <input
                type="text"
                v-model="globalSearchQuery"
                placeholder=" Search careers..."
                class="global-search-bar text-black px-4 py-2 rounded-lg w-full sm:w-3/4 md:w-1/2 lg:w-1/3"
              />
            </div>
          </section>

          <!-- UPCOMING CAREERS -->
          <section class="upcoming">
            <div class="flex items-center justify-between">
              <h2 class="section-title flex items-center gap-1">
                Open Careers
                <span class="count-badge">{{ sortedUpcomingCareers.length }}</span>
              </h2>
              <button
                class="plus-btn-text"
                @click="openCareerPopup()"
                :disabled="!isOrganizationVerified"
                :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''"
              >+</button>
            </div>

            <div class="career-grid">
              <div
                class="career-card"
                v-for="career in visibleFilteredUpcoming"
                :key="career.careerID || career.id"
                @click="openCareerDetails(career)"
              >
                <div class="career-right">
                  <h3 class="career-title">{{ career.position }}</h3>
                  <p class="career-deadline">
                    Closing: {{ formatdeadline(career.closingDate || career.deadlineOfSubmission) }}
                  </p>
                </div>

                <div class="menu">
                  <div
                    class="menu-icon"
                    @click.stop="toggleUpcomingMenu(career.careerID || career.id)"
                  >
                    ⋮
                  </div>
                  <div
                    v-if="openUpcomingMenu === (career.careerID || career.id)"
                    class="dropdown-menu"
                    @click.stop
                  >
                    <ul>
                      <li
                        @click="deleteCareer(career)"
                        :class="{ 'disabled-action': !isOrganizationVerified }"
                        :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''"
                      >
                        Delete Career
                      </li>
                      <li
                        @click="openCareerPopup(career)"
                        :class="{ 'disabled-action': !isOrganizationVerified }"
                        :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''"
                      >
                        Update Career
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <button
              v-if="sortedUpcomingCareers.length > 4"
              class="show-more-btn"
              @click="showAllUpcoming = !showAllUpcoming"
            >
              {{ showAllUpcoming ? "Show Less" : "Show More" }}
            </button>
          </section>

          <!-- COMPLETED CAREERS -->
          <section class="completed mt-8">
            <div class="flex items-center justify-between">
              <h2 class="section-title flex items-center gap-1">
                Closed Careers
                <span class="count-badge">{{ sortedCompletedCareers.length }}</span>
              </h2>
            </div>

            <div class="career-grid">
              <div
                class="career-card"
                v-for="career in visibleFilteredCompleted"
                :key="career.careerID || career.id"
                @click="openCareerDetails(career)"
              >
                <div class="career-right">
                  <h3 class="career-title">{{ career.title || career.position }}</h3>
                  <p class="career-deadline">
                    Closed: {{ formatdeadline(career.closingDate || career.deadlineOfSubmission) }}
                  </p>
                </div>

                <div class="menu">
                  <div
                    class="menu-icon"
                    @click.stop="toggleCompletedMenu(career.careerID || career.id)"
                  >
                    ⋮
                  </div>
                  <div
                    v-if="openCompletedMenu === (career.careerID || career.id)"
                    class="dropdown-menu"
                    @click.stop
                  >
                    <ul>
                      <li
                        @click="deleteCareer(career)"
                        :class="{ 'disabled-action': !isOrganizationVerified }"
                        :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''"
                      >
                        Delete Career
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <button
              v-if="sortedCompletedCareers.length > 4"
              class="show-more-btn"
              @click="showAllCompleted = !showAllCompleted"
            >
              {{ showAllCompleted ? "Show Less" : "Show More" }}
            </button>
          </section>
        </div>
      </div>

      <!-- Schedule Modal -->
      <div v-if="showScheduleModal" class="modal-overlay schedule-modal-overlay" @click.self="closeScheduleModal">
        <div class="modal-box">
          <button class="modal-close-btn" @click="closeScheduleModal">✕</button>
          <h3>{{ selectedPerson?.interviewSchedule ? 'Update Interview Schedule' : 'Schedule Interview' }} for {{
            selectedPerson?.name }}</h3>

          <div class="schedule-form">
            <!-- Date & Time -->
            <label for="scheduleDate">Date & Time:</label>
            <div class="date-input-wrapper">
              <input
                id="scheduleDate"
                ref="dateInput"
                type="datetime-local"
                v-model="scheduleData.date"
                @keydown.prevent
                @keypress.prevent
                @paste.prevent
                @input="$event.target.value = $event.target.value"
              />
              <span class="calendar-icon" @click.prevent="openCalendar">
                <!-- SVG icon (click target is the span) -->
                <svg
                  width="26"
                  height="26"
                  viewBox="0 0 26 26"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M2.16669 9.41675C2.16669 7.53113 2.16669 6.58832 2.75247 6.00253C3.33826 5.41675 4.28107 5.41675 6.16669 5.41675H19.8334C21.719 5.41675 22.6618 5.41675 23.2476 6.00253C23.8334 6.58832 23.8334 7.53113 23.8334 9.41675V9.83342C23.8334 10.3048 23.8334 10.5405 23.6869 10.687C23.5405 10.8334 23.3048 10.8334 22.8334 10.8334H3.16669C2.69528 10.8334 2.45958 10.8334 2.31313 10.687C2.16669 10.5405 2.16669 10.3048 2.16669 9.83341V9.41675Z"
                    fill="black"
                  />
                  <path
                    d="M22.833 13C23.3042 13 23.5401 13.0002 23.6865 13.1465C23.833 13.2929 23.833 13.5286 23.833 14V19.833C23.833 21.7186 23.8329 22.6613 23.2471 23.2471C22.6613 23.8329 21.7186 23.833 19.833 23.833H6.16699C4.28137 23.833 3.33872 23.8329 2.75293 23.2471C2.16714 22.6613 2.16699 21.7186 2.16699 19.833V14C2.16699 13.5286 2.16703 13.2929 2.31348 13.1465C2.45994 13.0002 2.69576 13 3.16699 13H22.833Z"
                    fill="black"
                  />
                  <path
                    d="M7.58331 3.25L7.58331 6.5"
                    stroke="black"
                    stroke-width="2"
                    stroke-linecap="round"
                  />
                  <path
                    d="M18.4167 3.25L18.4167 6.5"
                    stroke="black"
                    stroke-width="2"
                    stroke-linecap="round"
                  />
                </svg>
              </span>
            </div>

            <!-- Interview Mode -->
            <div class="mode-selection">
              <label
                ><input
                  type="radio"
                  value="onSite"
                  v-model="scheduleData.mode"
                />
                On-Site</label
              >
              <label
                ><input
                  type="radio"
                  value="online"
                  v-model="scheduleData.mode"
                />
                Online</label
              >
            </div>

            <!-- Conditional Fields -->
            <div v-if="scheduleData.mode === 'onSite'">
              <label>Location:</label>
              <input
                type="text"
                v-model="scheduleData.detail"
                placeholder="Enter interview location"
                class="schedule-input"
              />
            </div>

            <div v-else-if="scheduleData.mode === 'online'">
              <label>Interview Link:</label>
              <input
                type="text"
                v-model="scheduleData.detail"
                placeholder="https://meet.google.com/..."
                class="schedule-input"
              />
            </div>

            <div class="input-group full-width">
              <label>Email CC:</label>
              <input
                type="text"
                v-model="scheduleData.cc"
                placeholder="cc@example.com"
                class="schedule-input"
              />
            </div>

            <div class="input-group full-width">
              <label>Email Body:</label>
              <textarea
                v-model="scheduleData.body"
                rows="4"
                placeholder="Include message details for the applicant"
                class="schedule-textarea"
              ></textarea>
            </div>

            <div class="modal-actions">
              <div class="schedule-dropdown-wrapper">
                <div class="schedule-dropdown-container">
                  <button 
                    class="confirm-btn schedule-dropdown-main-btn" 
                    @click="confirmSchedule"
                  >
                    {{ getScheduleButtonText() }}
                  </button>
                  <button 
                    class="schedule-dropdown-toggle" 
                    :class="{ 'dropdown-open': showScheduleDropdown }"
                    @click.stop="showScheduleDropdown = !showScheduleDropdown"
                    @blur="handleScheduleDropdownBlur"
                  >
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="dropdown-arrow">
                      <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div v-if="showScheduleDropdown" class="schedule-dropdown-menu" @click.stop>
                  <button 
                    class="schedule-dropdown-item" 
                    :class="{ active: scheduleAction === 'scheduleOnly' }"
                    @click="selectScheduleAction('scheduleOnly')"
                  >
                    Schedule only
                  </button>
                  <button 
                    class="schedule-dropdown-item" 
                    :class="{ active: scheduleAction === 'scheduleAndEmail' }"
                    @click="selectScheduleAction('scheduleAndEmail')"
                  >
                    Schedule and Email
                  </button>
                </div>
              </div>
              <button class="cancel-btn" @click="closeScheduleModal">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Conflict Warning Modal -->
      <div
        v-if="showConflictModal"
        class="modal-overlay"
        @click.self="cancelConflictSchedule"
      >
        <div class="modal-box">
          <button class="modal-close-btn" @click="cancelConflictSchedule">
            ✕
          </button>
          <h3>Potential Schedule Conflict</h3>
          <p class="conflict-message">
            Another applicant
            <strong>{{ conflictInfo?.name }}</strong>
            already has an interview scheduled at
            <strong>{{
              conflictInfo?.interviewSchedule
                ? formatInterviewDateTime(conflictInfo.interviewSchedule)
                : "the same time"
            }}</strong
            >.
          </p>
          <p class="conflict-message">
            This interview schedule overlaps with the existing one. Scheduling both interviews at overlapping times may cause conflicts.
          </p>
          <p class="conflict-message">Do you still want to proceed with scheduling this interview?</p>
          <div class="modal-actions">
            <button class="confirm-btn" @click="confirmConflictSchedule">
              Continue
            </button>
            <button class="cancel-btn" @click="cancelConflictSchedule">
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- View Schedule Modal -->
      <div
        v-if="showViewScheduleModal"
        class="modal-overlay"
        @click.self="closeViewScheduleModal"
      >
        <div class="modal-box">
          <button class="modal-close-btn" @click="closeViewScheduleModal">
            ✕
          </button>
          <h3>Interview Schedule for {{ selectedPerson?.name }}</h3>

          <div class="view-schedule-info" v-if="selectedPerson">
            <p v-if="selectedPerson.interviewSchedule">
              <strong>Date & Time:</strong>
              {{ formatInterviewDateTime(selectedPerson.interviewSchedule) }}
            </p>
            <p v-if="selectedPerson.interviewMode">
              <strong>Mode:</strong> {{ selectedPerson.interviewMode }}
            </p>

            <p
              v-if="
                selectedPerson.interviewMode === 'On-Site' &&
                selectedPerson.interviewLocation
              "
            >
              <strong>Location:</strong> {{ selectedPerson.interviewLocation }}
            </p>
            <p
              v-else-if="
                selectedPerson.interviewMode === 'Online' &&
                selectedPerson.interviewLink
              "
            >
              <strong>Interview Link:</strong>
              <a
                :href="selectedPerson.interviewLink"
                target="_blank"
                rel="noopener noreferrer"
              >
                {{ selectedPerson.interviewLink }}
              </a>
            </p>
          </div>

          <div class="modal-actions">
            <button class="cancel-btn" @click="closeViewScheduleModal">
              Close
            </button>
          </div>
        </div>
      </div>

      <!-- Status Email Modal -->
      <div v-if="showStatusEmailModal" class="modal-overlay schedule-modal-overlay" @click.self="closeStatusEmailModal">
        <div class="modal-box">
          <button class="modal-close-btn" @click="closeStatusEmailModal">✕</button>
          <h3>
            Send {{ selectedPerson?.status === 'hired' ? 'Hired' : 'Declined' }} Status Email
            <span v-if="selectedPerson?.name">to {{ selectedPerson.name }}</span>
          </h3>

          <div class="schedule-form">
            <div class="input-group full-width">
              <label>Custom Message (Optional):</label>
              <textarea
                v-model="statusEmailData.body"
                rows="6"
                placeholder="Add a custom message to include in the email. If left empty, the default message will be sent."
                class="schedule-textarea"
              ></textarea>
              <p class="help-text">This message will be included in the email notification sent to the applicant.</p>
            </div>

            <div class="modal-actions">
              <button 
                class="confirm-btn" 
                @click="sendStatusEmail"
                :disabled="sendingStatusEmail"
              >
                <span v-if="sendingStatusEmail">Sending...</span>
                <span v-else>📧 Send Email</span>
              </button>
              <button class="cancel-btn" @click="closeStatusEmailModal" :disabled="sendingStatusEmail">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Career Popup Modal -->
      <div v-if="showCareerPopup" class="career-popup-overlay">
        <div class="career-popup">
          <!-- Close Button -->
          <button @click="closeCareerPopup" class="career-popup-close">
            ✕
          </button>

          <!-- Title -->
          <h2 class="career-popup-title">
            {{ isEditMode ? "Update Career" : "Post Career" }}
          </h2>

          <form @submit.prevent="saveCareer" class="Career-popup-form">
            <!-- Inputs -->
            <div class="input-with-counter">
              <input v-model="newCareer.position" type="text" placeholder="Position" class="career-input" required maxlength="100" />
              <span class="char-counter">{{ (newCareer.position || '').length }}/100</span>
            </div>
            <div class="input-with-counter">
              <input v-model="newCareer.placeOfAssignment" type="text" placeholder="Place of Assignment" class="career-input" required maxlength="100" />
              <span class="char-counter">{{ (newCareer.placeOfAssignment || '').length }}/100</span>
            </div>
            <div class="input-with-counter">
              <textarea v-model="newCareer.details" :placeholder="newCareer.pdfPath ? 'Details (optional if PDF uploaded)' : 'Details (required if no PDF)'" class="career-input" maxlength="1000"></textarea>
              <span class="char-counter">{{ (newCareer.details || '').length }}/1000</span>
            </div>
            <div class="input-with-counter">
              <textarea v-model="newCareer.qualificationStandard" :placeholder="newCareer.pdfPath ? 'Qualification Standard (optional if PDF uploaded)' : 'Qualification Standard (required if no PDF)'" class="career-input" maxlength="1000"></textarea>
              <span class="char-counter">{{ (newCareer.qualificationStandard || '').length }}/1000</span>
            </div>
            <div class="career-upload-wrapper">
              <label class="career-upload-label">Attach PDF (optional)</label>
              <input ref="careerPdfInput" type="file" accept="application/pdf" class="career-input"
                @change="handleCareerPdfUpload" />
              <p class="upload-help-text">Accepted format: PDF up to 10MB.</p>
              <p v-if="careerPdfUploading" class="upload-status">Uploading PDF...</p>
              <p v-if="careerPdfError" class="upload-error">{{ careerPdfError }}</p>
              <div v-if="newCareer.pdfPath" class="uploaded-file">
                <span class="uploaded-file-name">{{ careerPdfName || "uploaded.pdf" }}</span>
                <div class="upload-actions">
                  <button type="button" class="upload-action" @click="viewCareerPdf">View</button>
                  <button type="button" class="upload-action remove" @click="removeCareerPdf">Remove</button>
                </div>
              </div>
            </div>

            <!-- Posting Date -->
            <div class="deadline-input-wrapper">
              <label class="career-upload-label">Posting Date</label>
              <input
                type="date"
                v-model="newCareer.postingDate"
                :min="todayDate"
                placeholder="Posting Date"
                class="career-input"
                required
              />
            </div>

            <!-- Closing Date -->
            <div class="deadline-input-wrapper">
              <label class="career-upload-label">Closing Date</label>
              <input
                type="date"
                v-model="newCareer.closingDate"
                :min="newCareer.postingDate || todayDate"
                placeholder="Closing Date"
                class="career-input"
                required
              />
            </div>

            <!-- Tags -->
            <div class="relative mb-4">
              <label class="block font-semibold text-gray-600 mb-2">Tags</label>
              <input
                v-model="newTagName"
                type="text"
                placeholder="Search or type a new tag..."
                class="career-input mb-2"
              />
              <div class="tag-list-wrapper">
                <div class="tag-list">
                  <span
                    v-for="tag in filteredTags"
                    :key="tag.TagID"
                    class="tag-chip"
                    @click="toggleTag(tag.TagID)"
                    :class="{ 'tag-chip-selected': isTagSelected(tag.TagID) }"
                  >
                    {{ tag.TagName }}
                  </span>
                </div>
              </div>

              <div
                v-if="newCareer.Tags.length"
                class="flex flex-wrap gap-2 mt-2"
              >
                <span
                  v-for="tagID in newCareer.Tags"
                  :key="tagID"
                  class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full"
                >
                  {{ getTagName(tagID) }}
                  <button
                    @click.stop="removeTag(tagID)"
                    class="ml-1 text-red-500"
                  >
                    ×
                  </button>
                </span>
              </div>

              <button @click.prevent="addTag" class="career-save-btn mt-2">
                Add Tag
              </button>
            </div>

            <!-- Submit -->
            <button type="submit" class="career-save-btn" :disabled="!isOrganizationVerified" :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
              {{ isEditMode ? "Update" : "Post" }}
            </button>
          </form>
        </div>
      </div>

      <!-- Career Details Modal -->
      <div
        v-if="showCareerDetailsModal"
        class="modal-overlay"
        @click.self="closeCareerDetails"
      >
        <div class="career-details-modal">
          <button class="modal-close-btn" @click="closeCareerDetails">✕</button>
          <h3 class="modal-title">{{ selectedCareer.position }}</h3>
          <p class="career-info">
            <span class="career-group">
              <strong>Position:</strong>
              <span>{{ selectedCareer.position }}</span>
            </span>
            <span class="career-group">
              <strong>Place of Assignment:</strong>
              <span>{{ selectedCareer.placeOfAssignment || selectedCareer.applicationLetterAddress }}</span>
            </span>
          </p>
          <p class="career-info" v-if="selectedCareer.details || selectedCareer.detailsAndInstructions">
            <span class="career-group career-group-fullwidth">
              <strong>Details:</strong>
              <span class="career-text-value">{{ selectedCareer.details || selectedCareer.detailsAndInstructions }}</span>
            </span>
          </p>
          <p class="career-info" v-if="selectedCareer.qualificationStandard || selectedCareer.qualifications">
            <span class="career-group career-group-fullwidth">
              <strong>Qualification Standard:</strong>
              <span class="career-text-value">{{ selectedCareer.qualificationStandard || selectedCareer.qualifications }}</span>
            </span>
          </p>
          <p class="career-info">
            <span class="career-group">
              <strong>Posting Date:</strong>
              <span>{{ formatDate(selectedCareer.postingDate) }}</span>
            </span>
            <span class="career-group">
              <strong>Closing Date:</strong>
              <span>{{ formatdeadline(selectedCareer.closingDate || selectedCareer.deadlineOfSubmission) }}</span>
            </span>
          </p>
          <p class="career-info" v-if="selectedCareer.trainingsAttendedPercentage !== null && selectedCareer.trainingsAttendedPercentage !== undefined">
            <span class="career-group">
              <strong>Trainings Attended Percentage:</strong>
              <span>{{ selectedCareer.trainingsAttendedPercentage }}%</span>
            </span>
          </p>
          <p
            class="career-info"
            v-if="selectedCareerPdfName"
          >
            <strong>Attached PDF:</strong>
            <span class="uploaded-file-name">
              {{ selectedCareerPdfName }}
            </span>
            <button type="button" class="upload-action view-link" @click="viewSelectedCareerPdf">
              View PDF
            </button>
          </p>
          <div class="applicants-section">
            <div class="applicants-header">
              <h4>Applicants ({{ filteredApplicants.length }})</h4>
              <button class="applicants-refresh-btn" @click="loadApplicantsForCareer(selectedCareer)"
                :disabled="applicantsLoading">
                {{ applicantsLoading ? "Refreshing..." : "Refresh" }}
              </button>
            </div>

            <!-- Search Bar for Applicants -->
            <div class="applicants-search-wrapper" v-if="applicantsList.length > 0 && !applicantsLoading">
              <div class="relative">
                <input type="text" v-model="applicantSearchQuery" placeholder="Search by name or status..."
                  class="applicants-search-input" maxlength="100" />
                <span class="char-counter-search">{{ (applicantSearchQuery || '').length }}/100</span>
              </div>
            </div>

            <p v-if="applicantsError" class="applicants-error">
              {{ applicantsError }}
            </p>
            <p v-else-if="applicantsLoading" class="applicants-loading">
              Loading applicants...
            </p>
            <p v-else-if="applicantsList.length === 0" class="applicants-empty">
              No applicants found for this career.
            </p>
            <p v-else-if="filteredApplicants.length === 0" class="applicants-empty">
              No applicants match your search.
            </p>
            <div v-else-if="filteredApplicants.length > 0" class="applicants-table-container inline">
              <table>
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Requirements</th>
                    <th>Schedule Interview</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="person in filteredApplicants" :key="person.id">
                    <td>
                      <p class="applicant-name">{{ person.name }}</p>
                    </td>
                    <td>
                      <p class="application-date">
                        {{ person.dateSubmitted }}
                      </p>
                    </td>
                    <td>
                      <div class="status-cell-wrapper">
                        <select 
                          v-model="person.status" 
                          @change="updateApplicationStatus(person, $event)" 
                          class="status-dropdown"
                        >
                          <option value="submitted">
                            {{ displayStatus("submitted") }}
                          </option>
                          <option value="for review">
                            {{ displayStatus("for review") }}
                          </option>
                          <option value="for interview">
                            {{ displayStatus("for interview") }}
                          </option>
                          <option value="hired">
                            {{ displayStatus("hired") }}
                          </option>
                          <option value="declined">
                            {{ displayStatus("declined") }}
                          </option>
                        </select>
                        <button
                          v-if="person.status === 'hired' || person.status === 'declined' || person.status === 'rejected'"
                          @click="openStatusEmailModal(person)"
                          class="resend-email-btn"
                          title="Send status change email"
                        >
                          📧
                        </button>
                      </div>
                    </td>
                    <td class="requirements-col">
                      <button class="download-btn" @click="viewRequirements(person.id, person.requirement_directory)">
                        View Requirements
                      </button>
                    </td>
                    <td>
                      <button v-if="
                        person.status === 'for interview' &&
                        !person.interviewSchedule
                      " class="schedule-btn" @click="openScheduleModal(person)">
                        Schedule
                      </button>
                      <div v-else-if="
                        person.status === 'for interview' &&
                        person.interviewSchedule
                      " class="schedule-card clickable" @click="openScheduleModal(person)">
                        <div class="schedule-card-header">
                          <div class="schedule-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                          <div class="schedule-date-time">
                            {{ formatInterviewDateTime(person.interviewSchedule) }}
                          </div>
                        </div>
                        <div class="schedule-card-body">
                          <div class="schedule-badge"
                            :class="person.interviewMode === 'On-Site' ? 'badge-onsite' : 'badge-online'">
                            <svg v-if="person.interviewMode === 'On-Site'" width="12" height="12" viewBox="0 0 24 24"
                              fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                              <path
                                d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M16 8C16 6.93913 15.5786 5.92172 14.8284 5.17157C14.0783 4.42143 13.0609 4 12 4C10.9391 4 9.92172 4.42143 9.17157 5.17157C8.42143 5.92172 8 6.93913 8 8C8 9.06087 8.42143 10.0783 9.17157 10.8284C9.92172 11.5786 10.9391 12 12 12C13.0609 12 14.0783 11.5786 14.8284 10.8284C15.5786 10.0783 16 9.06087 16 8Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                              <path d="M12 14C16.4183 14 20 15.7909 20 18V20H4V18C4 15.7909 7.58172 14 12 14Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ person.interviewMode }}</span>
                          </div>
                          <div class="schedule-info" v-if="
                            person.interviewMode === 'On-Site' &&
                            person.interviewLocation
                          ">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                              <path
                                d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ person.interviewLocation }}</span>
                          </div>
                          <div class="schedule-info" v-else-if="
                            person.interviewMode === 'Online' &&
                            person.interviewLink
                          ">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path d="M18 13V16A2 2 0 0 1 16 18H5A2 2 0 0 1 3 16V8A2 2 0 0 1 5 6H10"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                              <path d="M15 3H21V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                              <path d="M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            </svg>
                            <a :href="person.interviewLink" target="_blank" rel="noopener noreferrer"
                              class="schedule-link" @click.stop>
                              {{ person.interviewLink }}
                            </a>
                          </div>
                        </div>
                      </div>
                      <span v-else>-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed top-5 right-5 space-y-2 z-50">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'px-4 py-2 rounded shadow flex items-center gap-2',
            toast.type === 'success'
              ? 'bg-white text-black'
              : toast.type === 'error'
              ? 'bg-red-500 text-white'
              : toast.type === 'confirm'
              ? 'bg-dark-slate text-white'
              : 'bg-gray-500 text-white',
          ]"
        >
          <span class="flex-1">{{ toast.message }}</span>

          <!-- ONLY SHOW WHEN CONFIRM -->
          <template v-if="toast.type === 'confirm'">
            <button
              @click="toast.onConfirm()"
              class="px-2 py-1 bg-white text-black rounded"
            >
              Yes
            </button>
            <button
              @click="toast.onCancel()"
              class="px-2 py-1 bg-gray-700 text-white rounded"
            >
              No
            </button>
          </template>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.status-cell-wrapper {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  min-width: 0;
}

.status-dropdown {
  padding: 6px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  background-color: white;
  cursor: pointer;
  flex: 1;
  min-width: 120px;
}

.resend-email-btn {
  background-color: #44576D;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 6px 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  flex-shrink: 0;
}

.resend-email-btn:hover {
  background-color: #334155;
  transform: scale(1.05);
}

.resend-email-btn:active {
  transform: scale(0.98);
}

/* Responsive adjustments for status cell */
@media (max-width: 768px) {
  .status-cell-wrapper {
    flex-direction: column;
    align-items: stretch;
    gap: 4px;
  }
  
  .status-dropdown {
    width: 100%;
    min-width: 0;
  }
  
  .resend-email-btn {
    width: 100%;
    min-width: 0;
  }
}

.view-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-btn:hover {
  background-color: #1e293b;
  transform: scale(1.03);
}

.tag-list-wrapper {
  overflow-x: auto;
  white-space: nowrap;
  padding: 5px 0;
}

.tag-list {
  display: flex;
}

.tag-chip {
  cursor: pointer;
  background-color: #e2e8f0;
  padding: 8px 12px;
  border-radius: 16px;
  margin-right: 8px;
  transition: background-color 0.2s;
  flex-shrink: 0;
}

.tag-chip:hover {
  background-color: #cbd5e0;
}

.tag-chip-selected {
  background-color: #4a5568;
  color: white;
}

/* Optional fade animation */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.organization-careers {
  display: flex;
  height: 100vh;
  font-family: "Poppins", sans-serif;
  background-color: #f4f4f4;
}

/* Sidebar */
.sidebar {
  width: 230px;
  /* Expanded width - increased to fit Change Password option */
  background-color: #44576d;
  color: white;
  display: flex;
  flex-direction: column;
  align-items: start;
  padding: 20px 10px;
  transition: width 0.3s ease-in;
  overflow: hidden;
}

.sidebar.collapsed {
  width: 60px;
  /* Collapsed width */
}

.sidebar .icon {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0;
  font-size: 16px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  width: 100%;
  white-space: nowrap;
  transition: background-color 0.2s ease;
}

.sidebar .icon svg {
  flex-shrink: 0;
  /* Ensures icon doesn't shrink */
  width: 30px;
  height: 30px;
}

.sidebar .icon:hover {
  background-color: #4a5568;
}

.sidebar .icon span {
  transition: opacity 0.3s ease;
  opacity: 1;
}

.sidebar.collapsed .icon span {
  opacity: 0;
  pointer-events: none;
}

.sidebar.collapsed .avatar {
  margin: 10px auto;
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.sidebar.collapsed .space {
  margin: 10px auto;
  width: 40px;
  height: 5px;
  border-radius: 50%;
}

.icon.active {
  background-color: #ffffff33;
  /* faint highlight */
  color: #000000;
}

.icon.active svg path {
  fill: #000000;
  /* change icon color when active */
}

/* Main Content */
.content {
  flex: 1;
  padding: 30px 40px;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: center;
  /* Keep it centered */
  align-items: center;
  margin-bottom: 40px;
}

.logo-title {
  font-size: 32px;
  font-weight: 700;
  color: #2d3748;
}

.logo-text {
  font-size: 26px;
  font-weight: 700;
  color: #44576d;
  font-family: "Poppins", sans-serif;
}

.search-container {
  position: relative;
  width: 500px;
  max-width: 100%;
}

.section-block {
  margin: 50px auto;
  max-width: 1000px;
  padding: 0 20px;
  text-align: center;
}

.section-block h2 {
  font-size: 26px;
  color: #2d3748;
  margin-bottom: 20px;
  text-align: center;
}

.applicants-btn {
  background-color: #44576d;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}

.applicants-btn {
  background-color: #44576d;
}

.applicants-btn:hover {
  background-color: #5a667d;
}

.spacer {
  flex: 1;
  /* pushes signout to bottom */
}

.sidebar .signout {
  margin-top: auto;
  /* ensures it's at the bottom */
}

.profile-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  /* centers everything under avatar */
  text-align: center;
  gap: 0.5rem;
  /* spacing between avatar, name, and actions */
  width: 100%;
}

.profile-section .avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: #ccc;
  /* placeholder for logo/avatar */
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.2);
}

.avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #ccc;
  margin: 20px auto 10px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.org-name {
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  width: 100%;
  margin: 0 auto;
  line-height: 1.4;
}

.org-status-inline {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 500;
  padding: 2px 8px;
  border-radius: 999px;
  margin-left: 6px;
  vertical-align: middle;
}

.org-status-icon-inline {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.org-status-verified {
  background-color: #10b981;
  color: #ffffff;
  border: 1px solid #059669;
}

.org-status-unverified {
  background-color: #f59e0b;
  color: #ffffff;
  border: 1px solid #d97706;
}

.profile-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
}

.profile-actions .action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  /* smaller space between icon + text */
  font-size: 13px;
  /* smaller text */
  color: #fff;
  /* keep text color consistent */
  cursor: pointer;
  width: 100%;
  text-align: center;
}

.profile-actions .action svg {
  width: 14px;
  /* shrink the icon */
  height: 14px;
}

.profile-actions .action:hover {
  opacity: 0.8;
}

/* Training and Job Offer Area*/
.career-slider {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.career-grid {
  display: grid;
  gap: 1.5rem;
  margin-top: 1rem;
  overflow: visible;
  transition: max-height 0.4s ease;
}

/* Collapsed view */
.career-grid.collapsed {
  max-height: 600px;
  /* adjust depending on your card height */
}

/* Expanded view */
.career-grid.expanded {
  max-height: 2000px;
  /* or something large enough */
  overflow: visible;
}

/* Default - Large screens (4 per row) */
.career-grid {
  grid-template-columns: repeat(4, 1fr);
}

/* Medium screens (3 per row) */
@media (max-width: 1200px) {
  .career-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Small screens (2 per row) */
@media (max-width: 900px) {
  .career-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Extra small screens (1 per row) */
@media (max-width: 600px) {
  .career-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

.upcoming {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.completed {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 15px;
  color: #2d3748;
}

.career-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  transition: transform 0.2s ease;
}

.career-card:hover {
  transform: translateY(-4px);
}

.career-left {
  margin-right: 12px;
}

.career:last-child {
  border-bottom: none;
}

.career-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #2d3748;
}

.career-deadline {
  font-size: 13px;
  color: #4a5568;
}

.career-company {
  font-size: 14px;
  color: #718096;
  margin-bottom: 10px;
}

.edit-btn {
  background-color: #44576d;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}

.apply-btn:hover {
  background-color: #5a667d;
}

.menu {
  position: absolute;
  top: 10px;
  right: 10px;
}

.menu-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
  color: #000;
}

.menu-icon {
  cursor: pointer;
  font-size: 20px;
  color: #000;
  position: absolute;
  top: 2px;
  right: 5px;
}

.menu-dropdown {
  position: absolute;
  top: 30px;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
  width: 120px;
  z-index: 10;
}

.menu-dropdown p {
  margin: 0;
  padding: 8px 12px;
  font-size: 14px;
  cursor: pointer;
}

.menu-dropdown p:hover {
  background: #f7fafc;
}

.dropdown-menu {
  position: absolute;
  top: 25px;
  right: 0;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  min-width: 160px;
  overflow: hidden;
}

.dropdown-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.dropdown-menu li {
  padding: 6px 12px;
  cursor: pointer;
  font-size: 14px;
  white-space: nowrap;
  color: #000;
  line-height: 1.4;
}

.dropdown-menu li + li {
  border-top: 1px solid #eee;
}

.dropdown-menu li:hover {
  background: #f7f7f7;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

/* Schedule Modal Overlay - appears above career details modal */
.schedule-modal-overlay {
  z-index: 2200;
}

.modal {
  background: white;
  padding: 25px;
  border-radius: 15px;
  width: 350px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 15px;
  gap: 10px;
}

.confirm-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.cancel-btn {
  background-color: #334155;
  color: #111;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 1100px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.Applicants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 20px;
  /* more space between cards */
  margin-top: 15px;
}

.registrant-card {
  background: #ffffff;
  /* solid white card */
  border: 1px solid #ddd;
  /* light gray border */
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
  /* stronger shadow */
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.registrant-card:hover {
  transform: translateY(-5px);
  /* lift on hover */
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}

.profile-pic {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 10px;
  display: block;
  margin-left: auto;
  margin-right: auto;
}

.registrant-card p {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #000;
  /* clear black text */
}

/* Counter Badge */
.count-badge {
  background-color: #374151;
  color: white;
  font-weight: bold;
  padding: 0.15rem 0.9rem;
  border-radius: 9999px;
  font-size: 0.9rem;
}

/* Post Career CSS */
.career-popup-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  z-index: 50;
}

.career-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: 600px;
  max-height: 90vh;
  padding: 24px;
  position: relative;
  overflow-y: auto;
}

.career-popup-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 18px;
  color: #555;
  cursor: pointer;
}

.career-popup-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.career-popup-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Custom scrollbar for career popup */
.career-popup::-webkit-scrollbar {
  width: 8px;
}

.career-popup::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.career-popup::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.career-popup::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.career-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  margin-bottom: 15px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
}

.career-save-btn {
  background: #374151;
  color: white;
  font-size: 14px;
  font-weight: 500;
  padding: 10px;
  border-radius: 6px;
  width: 100%;
  border: none;
  cursor: pointer;
}

.career-save-btn:hover {
  background: #1f2937;
}

.career-save-btn:disabled,
.plus-btn-text:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.disabled-action {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.career-upload-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  margin-bottom: 1rem;
}

.career-upload-label {
  font-weight: 600;
  color: #374151;
}

.upload-help-text {
  font-size: 0.85rem;
  color: #6b7280;
}

.upload-status {
  font-size: 0.85rem;
  color: #2563eb;
}

.upload-error {
  font-size: 0.85rem;
  color: #dc2626;
}

.uploaded-file {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #e5e7eb;
  border-radius: 8px;
  padding: 6px 10px;
}

.uploaded-file-name {
  font-weight: 500;
  color: #1f2937;
  flex: 1;
  margin-right: 10px;
  word-break: break-all;
}

.upload-actions {
  display: flex;
  gap: 6px;
}

.upload-action {
  background: none;
  border: none;
  color: #2563eb;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
}

.upload-action:hover {
  text-decoration: underline;
}

.upload-action.remove {
  color: #b91c1c;
}

.upload-action.view-link {
  padding-left: 0.4rem;
}

/* Posting Botton */
.plus-btn-text {
  background: none;
  border: none;
  font-size: 1.5rem;
  font-weight: bold;
  line-height: 1;
  color: #374151;
  cursor: pointer;
  transition: color 0.2s;
}

.plus-btn-text:hover {
  color: #000000;
}

/* Career Details */

.career-details-modal {
  background: #fff;
  padding: 2rem;
  border-radius: 1rem;
  width: min(95vw, 1200px);
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.25s ease;
  z-index: 2100;
  /* ensure above other overlays */
}

.career-info {
  margin: 0.75rem 0;
  color: #333;
  line-height: 1.6;
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
  gap: 1.5rem;
}

.career-group {
  display: inline-flex;
  align-items: baseline;
  flex: 1;
  min-width: 0;
}

.career-group strong {
  margin-right: 0.25rem;
  white-space: nowrap;
}

.career-group span {
  white-space: normal;
}

/* Full-width groups for details and qualification standard to prevent overlap */
.career-group-fullwidth {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  width: 100%;
  gap: 0.5rem;
}

.career-group-fullwidth strong {
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

.career-description {
  margin-top: 1rem;
  color: #555;
  line-height: 1.5;
}

.applicants-section {
  margin-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}

.applicants-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.applicants-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.applicants-search-wrapper {
  margin-bottom: 1rem;
}

.applicants-search-input {
  width: 100%;
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
  outline: none;
  transition: border-color 0.2s;
}

.applicants-search-input:focus {
  border-color: #4c6ef5;
  box-shadow: 0 0 0 3px rgba(76, 110, 245, 0.1);
}

.applicants-search-input::placeholder {
  color: #9ca3af;
}

/* Character Counter Styles */
.input-with-counter {
  position: relative;
  width: 100%;
  margin-bottom: 15px;
}

.char-counter {
  font-size: 12px;
  color: #6b7280;
  text-align: right;
  margin-top: 4px;
  display: block;
}

.char-counter-search {
  position: absolute;
  right: 10px;
  bottom: 8px;
  font-size: 12px;
  color: #6b7280;
  background-color: rgba(255, 255, 255, 0.9);
  padding: 2px 4px;
  border-radius: 4px;
}

.applicants-refresh-btn {
  background-color: #374151;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.applicants-refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.applicants-refresh-btn:not(:disabled):hover {
  background-color: #1f2937;
}

.applicants-error {
  color: #b91c1c;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.applicants-loading,
.applicants-empty {
  font-size: 0.9rem;
  color: #4b5563;
  margin-bottom: 0.75rem;
}

.applicants-table-container {
  overflow-x: auto;
  width: 100%;
  box-sizing: border-box;
  padding: 0;
  margin: 0;
}

.applicants-table-container.inline {
  max-height: 320px;
  overflow-y: auto;
  overflow-x: auto;
}

.applicants-table-container.inline table {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  min-width: 100%;
}

.applicants-table-container.inline th,
.applicants-table-container.inline td {
  padding: 8px 6px;
  font-size: 0.85rem;
  word-break: break-word;
}

.applicants-table-container.inline th:nth-child(1),
.applicants-table-container.inline td:nth-child(1) {
  width: 18%;
}

.applicants-table-container.inline th:nth-child(2),
.applicants-table-container.inline td:nth-child(2) {
  width: 18%;
}

.applicants-table-container.inline th:nth-child(3),
.applicants-table-container.inline td:nth-child(3) {
  width: 18%;
}

.applicants-table-container.inline th:nth-child(4),
.applicants-table-container.inline td:nth-child(4) {
  width: 18%;
}

.applicants-table-container.inline th:nth-child(5),
.applicants-table-container.inline td:nth-child(5) {
  width: 26%;
  /* Increased width for Schedule Interview column to accommodate schedule display */
}

.download-btn {
  white-space: normal;
  line-height: 1.2;
  padding: 6px 10px;
  width: 135px;
  max-width: 100%;
}

.schedule-btn {
  min-width: 105px;
  padding: 6px 10px;
  white-space: nowrap;
}

/* Smooth appear animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animate position when sidebar opens */
.hamburger {
  position: fixed;
  top: 15px;
  left: 18px;
  width: 25px;
  height: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: none;
  border: none;
  cursor: pointer;
  z-index: 2000;
  /* ← raised from 100 to 2000 */
  padding: 0;
  transition: transform 0.6s ease;
  /* smoother animation */
}

/* Hamburger lines */
.hamburger span {
  display: block;
  height: 3px;
  width: 100%;
  background-color: white;
  border-radius: 2px;
}

/* When sidebar is open, move hamburger to the right */
.hamburger.shifted {
  transform: translateX(170px);
  /* Adjust this to your sidebar width (230px - 60px = 170px) */
}

/* Calendar for Deadline of Submission */
.deadline-input-wrapper {
  position: relative;
}

.deadline-input-wrapper input[type="date"] {
  width: 100%;
  padding: 10px 40px 10px 10px;
  /* space for icon */
  border: 1px solid #ccc;
  border-radius: 6px;
  background: #fff;
  color: #000;
  font-size: 14px;
}

/* Hide default calendar icon (browser default) */
.deadline-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  right: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

/* Calendar icon position */
:deep(.deadline-input-wrapper .calendar-icon) {
  position: absolute;
  right: 12px;
  top: 37%;
  transform: translateY(-50%);
  pointer-events: none;
  display: flex;
  align-items: center;
  z-index: 2;
}

/* Applicants Modal */
.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.registrants-table-container {
  /* Ensures table fits on smaller screens if needed */
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  text-align: left;
  padding: 12px 15px;
  color: #777;
  font-size: 0.85rem;
  text-transform: uppercase;
  font-weight: 600;
  border-bottom: 1px solid #eee;
}

tbody tr {
  border-bottom: 1px solid #f5f5f5;
  /* Light separator line */
}

tbody td {
  padding: 15px 15px;
  color: #333;
  font-size: 0.95rem;
  /* Vertically aligns content in the middle */
  vertical-align: middle;
}

/* Status Colors (like in your image) */
.status-for-review {
  color: #ffa600;
  /* Green */
  font-weight: 500;
}

.status-scheduled {
  color: #4caf50;
  /* Green */
  font-weight: 500;
}

.status-hired {
  color: #374151;
  /* Green */
  font-weight: 500;
}

.status-rejected {
  color: #d30707;
  /* Blue */
  font-weight: 500;
}

/* Base Schedule Button */
.schedule-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

/* Download Requirements Button */
.download-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.download-btn:hover {
  background-color: #1e293b;
  transform: scale(1.03);
}

.schedule-btn:hover {
  background-color: #1e293b;
  transform: scale(1.03);
}

.schedule-btn:disabled {
  background-color: #cbd5e1;
  color: #64748b;
  cursor: not-allowed;
  transform: none;
}

/* Update Schedule Style */
.schedule-btn.update {
  background-color: #334155;
}

.schedule-btn.update:hover {
  background-color: #1e293b;
}

.schedule-btn.update:hover:not(:disabled) {
  background-color: #1e293b;
}

/* Schedule Card (replaces Schedule button when scheduled) - Modern Design */
.schedule-card {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 0.8rem;
  line-height: 1.5;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.schedule-card.clickable {
  cursor: pointer;
  user-select: none;
}

.schedule-card.clickable:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-color: #94a3b8;
  transform: translateY(-2px);
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.schedule-card:hover {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  border-color: #cbd5e1;
}

.schedule-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e2e8f0;
}

.schedule-icon {
  display: flex;
  align-items: center;
  color: #4c6ef5;
  flex-shrink: 0;
}

.schedule-date-time {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.85rem;
  flex: 1;
}

.schedule-card-body {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.schedule-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  width: fit-content;
  white-space: nowrap;
}

.schedule-badge.badge-onsite {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1e40af;
  border: 1px solid #93c5fd;
}

.schedule-badge.badge-online {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
  border: 1px solid #fcd34d;
}

.schedule-badge svg {
  flex-shrink: 0;
}

.schedule-info {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #475569;
  font-size: 0.75rem;
  padding: 4px 0;
}

.schedule-info svg {
  flex-shrink: 0;
  color: #64748b;
}

.schedule-info span {
  word-break: break-word;
  line-height: 1.4;
}

.schedule-link {
  color: #4c6ef5;
  text-decoration: none;
  word-break: break-all;
  font-weight: 500;
  transition: color 0.2s;
}

.schedule-link:hover {
  color: #3b5bdb;
  text-decoration: underline;
}

.dimmed {
  background-color: #22c55e;
  opacity: 0.7;
  cursor: not-allowed;
}

.blurred {
  filter: blur(1px) brightness(0.85);
  opacity: 0.6;
  pointer-events: none;
}

/* Rejected Applicants - Blur Effect */
.status-rejected + .interview-col .schedule-btn {
  filter: blur(1.2px) brightness(0.85);
  opacity: 0.6;
  pointer-events: none;
}

.interview-info {
  margin-top: 6px;
}

.interview-date {
  font-weight: 500;
  color: #374151;
}

.interview-time {
  font-size: 0.85rem;
  color: #6b7280;
}

/* View Schedule Modal */
.schedule-view p {
  margin: 8px 0;
  color: #111827;
}

.schedule-view a {
  color: #6d6d6d;
  text-decoration: none;
}

.schedule-view a:hover {
  text-decoration: underline;
}

/* Interview Modal Form */
.schedule-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 4px;
  display: block;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 4px;
  display: block;
}

input[type="datetime-local"],
input[type="text"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.9rem;
}

.mode-group {
  display: flex;
  justify-content: space-around;
}

.schedule-confirm-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 8px 16px;
  cursor: pointer;
  margin-top: 10px;
  align-self: center;
  font-weight: 500;
  transition: all 0.2s ease;
}

.schedule-confirm-btn:hover {
  background-color: #1e293b;
}

/* Schedule Modal and View Schedule Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

/* Schedule Modal Overlay - appears above career details modal */
.schedule-modal-overlay {
  z-index: 2200;
}

.modal-box {
  background: #fff;
  border-radius: 12px;
  padding: 24px 28px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  position: relative;
  animation: fadeIn 0.2s ease-in-out;
  max-height: 90vh;
  overflow-y: auto;
  overflow-x: visible;
}

/* Make schedule modal even bigger */
.schedule-modal-overlay .modal-box {
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  padding: 28px 32px;
  overflow-y: auto;
  overflow-x: hidden;
  margin: 20px;
}

/* Responsive modal styles */
@media (max-width: 768px) {
  .schedule-modal-overlay .modal-box {
    width: 95%;
    max-width: 95%;
    padding: 20px 16px;
    margin: 10px;
    max-height: 85vh;
  }
  
  .schedule-modal-overlay .modal-box h3 {
    font-size: 1.2rem;
    margin-bottom: 16px;
  }
  
  .schedule-modal-overlay .schedule-form {
    gap: 10px;
  }
  
  .schedule-modal-overlay .schedule-form label {
    font-size: 0.9rem;
    margin: 8px 0 6px;
  }
  
  .schedule-modal-overlay .schedule-form input[type="datetime-local"],
  .schedule-modal-overlay .schedule-form input[type="text"],
  .schedule-modal-overlay .schedule-textarea {
    padding: 10px 12px;
    font-size: 0.9rem;
  }
  
  .schedule-modal-overlay .modal-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .schedule-modal-overlay .confirm-btn,
  .schedule-modal-overlay .cancel-btn {
    width: 100%;
    padding: 10px 20px;
  }
}

@media (max-width: 480px) {
  .schedule-modal-overlay .modal-box {
    width: 98%;
    max-width: 98%;
    padding: 16px 12px;
    margin: 5px;
    max-height: 90vh;
  }
  
  .schedule-modal-overlay .modal-box h3 {
    font-size: 1.1rem;
    margin-bottom: 12px;
    line-height: 1.3;
  }
  
  .schedule-modal-overlay .schedule-textarea {
    min-height: 100px;
  }
}

/* Ensure modal actions area allows dropdown to overflow */
.schedule-modal-overlay .modal-actions {
  position: relative;
  overflow: visible;
  z-index: 1;
}

.modal-box h3 {
  font-size: 1.3rem;
  margin-bottom: 20px;
  color: #222;
  text-align: center;
  font-weight: 600;
}

/* Schedule modal title */
.schedule-modal-overlay .modal-box h3 {
  font-size: 1.4rem;
  margin-bottom: 24px;
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}

.date-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.calendar-icon {
  position: absolute;
  right: 10px;
  top: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-icon svg {
  fill: black;
  pointer-events: none;
}

.schedule-form label {
  display: block;
  font-weight: 500;
  margin: 8px 0 6px;
  color: #000;
  font-size: 0.95rem;
}

/* Schedule modal labels */
.schedule-modal-overlay .schedule-form label {
  margin: 10px 0 8px;
  font-size: 1rem;
}

.schedule-form input[type="datetime-local"],
.schedule-form input[type="text"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.95rem;
  margin-bottom: 8px;
  background: #fff;
  color: #000;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.schedule-form input[type="datetime-local"]:focus,
.schedule-form input[type="text"]:focus {
  outline: none;
  border-color: #4c6ef5;
  box-shadow: 0 0 0 3px rgba(76, 110, 245, 0.1);
}

/* Schedule modal inputs */
.schedule-modal-overlay .schedule-form input[type="datetime-local"],
.schedule-modal-overlay .schedule-form input[type="text"] {
  padding: 12px 14px;
  font-size: 1rem;
}

.input-group.full-width {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.schedule-input,
.schedule-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.95rem;
  margin-bottom: 8px;
  background: #fff;
  color: #000;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.schedule-input:focus,
.schedule-textarea:focus {
  outline: none;
  border-color: #4c6ef5;
  box-shadow: 0 0 0 3px rgba(76, 110, 245, 0.1);
}

.schedule-textarea {
  min-height: 100px;
  resize: vertical;
  font-family: inherit;
}

/* Schedule modal inputs */
.schedule-modal-overlay .schedule-input,
.schedule-modal-overlay .schedule-textarea {
  padding: 12px 14px;
  font-size: 1rem;
}

.schedule-modal-overlay .schedule-textarea {
  min-height: 120px;
}

.help-text {
  font-size: 0.85rem;
  color: #666;
  margin-top: 4px;
  margin-bottom: 12px;
  font-style: italic;
}

.mode-selection {
  display: flex;
  gap: 16px;
  margin: 10px 0;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.mode-selection label {
  cursor: pointer;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Schedule modal mode selection */
.schedule-modal-overlay .mode-selection {
  gap: 20px;
  padding: 16px;
  margin: 12px 0;
}

.schedule-modal-overlay .mode-selection label {
  font-size: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

/* Schedule modal actions */
.schedule-modal-overlay .modal-actions {
  margin-top: 24px;
  padding-top: 24px;
  gap: 12px;
}

.confirm-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: background 0.2s, transform 0.1s;
}

.confirm-btn:hover {
  background: #1e293b;
  transform: translateY(-1px);
}

.confirm-btn:active {
  transform: translateY(0);
}

/* Schedule modal buttons */
.schedule-modal-overlay .confirm-btn {
  padding: 12px 24px;
  font-size: 1rem;
}

.cancel-btn {
  background: #e5e7eb;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  color: #374151;
  transition: background 0.2s, transform 0.1s;
}

.confirm-btn:hover {
  background: #1e293b;
}

.cancel-btn:hover {
  background: #d1d5db;
  transform: translateY(-1px);
}

.cancel-btn:active {
  transform: translateY(0);
}

/* Schedule modal buttons */
.schedule-modal-overlay .cancel-btn {
  padding: 12px 24px;
  font-size: 1rem;
}

/* Schedule Dropdown Button */
.schedule-dropdown-wrapper {
  position: relative;
  display: inline-block;
  z-index: 1;
}

/* Ensure dropdown is above modal overlay */
.schedule-modal-overlay .schedule-dropdown-wrapper {
  z-index: 2300;
}

.schedule-dropdown-container {
  display: flex;
  align-items: center;
  gap: 0;
}

.schedule-dropdown-main-btn {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  border-right: none;
  flex: 1;
}

.schedule-dropdown-toggle {
  background: #334155;
  color: white;
  border: none;
  padding: 7px 8px;
  border-top-right-radius: 6px;
  border-bottom-right-radius: 6px;
  border-left: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.schedule-dropdown-toggle:hover {
  background: #1e293b;
}

.schedule-dropdown-toggle .dropdown-arrow {
  transition: transform 0.2s;
}

.schedule-dropdown-toggle.dropdown-open .dropdown-arrow {
  transform: rotate(180deg);
}

.schedule-dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 4px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  min-width: 180px;
  overflow: hidden;
  animation: fadeIn 0.2s ease-in-out;
}

/* Ensure dropdown menu appears above modal overlay */
.schedule-modal-overlay .schedule-dropdown-menu {
  z-index: 2300;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.schedule-dropdown-item {
  display: block;
  width: 100%;
  padding: 10px 16px;
  text-align: left;
  background: white;
  border: none;
  color: #374151;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.2s;
}

.schedule-dropdown-item:hover {
  background: #f3f4f6;
}

.schedule-dropdown-item.active {
  background: #e0e7ff;
  color: #4c6ef5;
  font-weight: 500;
}

.schedule-dropdown-item.active:hover {
  background: #c7d2fe;
}

.view-schedule-info p {
  margin: 4px 0;
  /* slightly tighter spacing */
  font-size: 0.95rem;
  color: #333;
}

.conflict-message {
  margin: 8px 0;
  color: #374151;
  line-height: 1.4;
}

/* Adjust modal width and spacing */
.schedule-modal,
.view-schedule-modal {
  width: 360px;
  /* narrower modal */
  max-width: 90%;
  background: white;
  padding: 18px 22px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.input-with-icon {
  position: relative;
  display: inline-block;
  width: 100%;
}

.input-with-icon input {
  width: 100%;
  padding: 8px 38px 8px 10px;
  /* space for the icon on the right */
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  font-size: 0.9rem;
  color: #000;
  box-sizing: border-box;
  height: 38px;
}

/* ✅ perfectly center the icon */
.input-with-icon .calendar-icon {
  position: absolute;
  right: 10px;
  top: 42%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  /* allows clicking through the icon */
}

.input-with-icon svg {
  width: 18px;
  height: 18px;
  fill: #000;
  /* solid black */
  opacity: 1;
  /* make sure it’s not faded */
}

/* Show more css */
.show-more-btn {
  background-color: #374151;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 4px 10px;
  /* smaller padding */
  font-size: 0.85rem;
  /* smaller text */
  margin: 6px auto 0;
  /* centered horizontally */
  display: block;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}

.show-more-btn:hover {
  background-color: #1d222b;
}

.show-more-btn:active {
  transform: scale(0.97);
}

/* Search Bar CSS*/
.global-search-bar {
  width: 100%;
  /* container controls width */
  max-width: 600px;
  /* optional */
  height: 46px;
  /* ✅ explicitly sets height */
  padding: 0 14px;
  /* horizontal padding only */
  border: 1px solid #aaaaaa !important;
  border-radius: 8px;
  background-color: #fff;
  outline: none;
  transition: all 0.2s ease;
  color: #000;
}

.global-search-bar::placeholder {
  color: #9ca3af;
  /* same as Tailwind’s text-gray-400 */
  font-style: italic;
  /* optional — match whatever Training uses */
  opacity: 1;
  /* ensures consistent rendering */
  font-size: 16px;
}

.global-search-bar:focus {
  border-color: #44576d;
  box-shadow: 0 0 5px rgba(68, 87, 109, 0.2);
}
</style>
