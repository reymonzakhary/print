import { parseISO, format, parse } from "date-fns";
import { toZonedTime } from "date-fns-tz";

export const useHelpers = () => {
  function formatDate(dateString, formatString = "yyyy-MM-dd HH:mm:ss") {
    const utcTimestamp = dateString;
    const userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const utcDate = parseISO(utcTimestamp);
    const zonedDate = toZonedTime(utcDate, userTimeZone);
    const formattedDate = format(zonedDate, formatString);
    return formattedDate;
  }

  function parseDate(dateString, format = "yyyy-MM-dd HH:mm:ss") {
    return parse(dateString, format);
  }

  // Expects yyyy-MM-dd HH:mm:ss formatted date
  const extractTimeFromFormattedDate = (formattedDate) =>
    formattedDate.split(" ")[1].split(":").slice(0, 2).join(":");
  const extractDateFromFormattedDate = (formattedDate) => formattedDate.split(" ")[0];

  function _buildFormData(formData, data, parentKey) {
    if (
      data &&
      typeof data === "object" &&
      !(data instanceof Date) &&
      !(data instanceof File) &&
      !(data instanceof Blob)
    ) {
      Object.keys(data).forEach((key) => {
        _buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
      });
    } else {
      const value = data == null ? "" : data;
      formData.append(parentKey, value);
    }
  }

  function objectToFormData(data) {
    const formData = new FormData();
    _buildFormData(formData, data);
    return formData;
  }

  return {
    formatDate,
    parseDate,
    extractTimeFromFormattedDate,
    extractDateFromFormattedDate,
    objectToFormData,
  };
};
