import { createClient } from "@supabase/supabase-js";

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL || "https://hmevengvfponcwslnyye.supabase.co";
const supabaseKey =
  import.meta.env.VITE_SUPABASE_SERVICE_ROLE_KEY ||
  import.meta.env.VITE_SUPABASE_ANON_KEY ||
  import.meta.env.VITE_SUPABASE_KEY;

export const supabase = createClient(supabaseUrl, supabaseKey);

const DEFAULT_BUCKET = "Requirements"; // same bucket for both

/**
 * Upload PDF file to Supabase Storage
 * @param {File} file - The PDF file to upload
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @param {string} folder - The folder inside the bucket ('requirement_directory' or 'certificate_directory')
 * @returns {Promise<string|null>} - Returns the file path if successful, null otherwise
 */
export async function uploadPDF(file, bucketName = DEFAULT_BUCKET, folder = "requirement_directory") {
  if (!file) return null;

  if (file.type !== "application/pdf") {
    console.error("Only PDF files are allowed.");
    return null;
  }

  const fileName = `${folder}/${Date.now()}_${file.name}`;
  console.log(`Uploading file to ${bucketName}/${folder}:`, fileName);

  try {
    const { data, error } = await supabase.storage.from(bucketName).upload(fileName, file, {
      cacheControl: "3600",
      upsert: false,
    });

    if (error) {
      console.error(`Supabase upload error (${bucketName}/${folder}):`, error);
      return null;
    }

    return fileName;
  } catch (error) {
    console.error(`Upload error (${bucketName}/${folder}):`, error);
    return null;
  }
}

/**
 * Upload PDF to Requirements folder
 */
export async function uploadRequirement(file, bucketName = DEFAULT_BUCKET) {
  return uploadPDF(file, bucketName, "requirement_directory");
}

/**
 * Upload PDF to Certificates folder
 */
export async function uploadCertificate(file, bucketName = "Requirements") {
  const filePath = await uploadPDF(file, bucketName, "certificate_directory");
  if (!filePath) return null;

  // Return both the object path (for DB) and public URL (for UI)
  const { data } = supabase.storage.from(bucketName).getPublicUrl(filePath);
  const publicUrl = data?.publicUrl || null;

  return { filePath, publicUrl };
}

export function getPDFUrl(filePath, bucketName = "Requirements") {
  // Validate filePath is a non-empty string
  if (!filePath || typeof filePath !== "string" || filePath.trim().length === 0) {
    console.error("getPDFUrl: Invalid filePath provided", filePath);
    return null;
  }

  try {
    const { data } = supabase.storage.from(bucketName).getPublicUrl(filePath.trim());
    return data?.publicUrl || null;
  } catch (error) {
    console.error("getPDFUrl: Error generating public URL", error);
    return null;
  }
}

/**
 * Upload image file to Supabase Storage
 * @param {File} file - The image file to upload
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @param {string} folder - The folder inside the bucket (default: 'org_logo_directory')
 * @returns {Promise<string|null>} - Returns the file path if successful, null otherwise
 */
export async function uploadImage(file, bucketName = DEFAULT_BUCKET, folder = "org_logo_directory") {
  if (!file) return null;

  // Validate image file types
  const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  if (!validImageTypes.includes(file.type)) {
    console.error("Only image files (JPEG, PNG, GIF, WebP) are allowed.");
    return null;
  }

  // Check file size (max 5MB)
  const MAX_SIZE = 5 * 1024 * 1024; // 5MB
  if (file.size > MAX_SIZE) {
    console.error("Image file is too large. Maximum size is 5MB.");
    return null;
  }

  const fileName = `${folder}/${Date.now()}_${file.name}`;
  console.log(`Uploading image to ${bucketName}/${folder}:`, fileName);

  try {
    const { data, error } = await supabase.storage.from(bucketName).upload(fileName, file, {
      cacheControl: "3600",
      upsert: false,
    });

    if (error) {
      console.error(`Supabase upload error (${bucketName}/${folder}):`, error);
      return null;
    }

    return fileName;
  } catch (error) {
    console.error(`Upload error (${bucketName}/${folder}):`, error);
    return null;
  }
}

/**
 * Get public URL for an image file
 * @param {string} filePath - The file path in Supabase
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @returns {string|null} - Public URL to the file
 */
export function getImageUrl(filePath, bucketName = DEFAULT_BUCKET) {
  return getPDFUrl(filePath, bucketName); // Same function works for images
}

/**
 * Get public URL for a PDF file
 * @param {string} fileName - The file path in Supabase
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @returns {string} - Public URL to the file
 */
