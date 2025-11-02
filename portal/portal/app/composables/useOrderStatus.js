import { useI18n } from "vue-i18n";
export const useOrderStatus = () => {
  const statusMap = {
    DRAFT: 300,
    PENDING: 301,
    NEW: 302,
    IN_PROGRESS: 303,
    BEING_SHIPPED: 304,
    CANCELED: 305,
    READY: 306,
    DELIVERED: 307,
    DONE: 308,
    LOCKED: 309,
    ARCHIVED: 310,
    BLOCKED: 311,
    MAILING: 312,
    MAILED: 313,
    PROCESSING: 314,
    EXPIRING: 315,
    EXPIRED: 316,
    EDITABLE: 317,
    IN_PRODUCTION: 318,
    REJECTED: 319,
    ACCEPTED: 320,
    FAILED: 321,
    WAITING_FOR_RESPONSE: 322,
    DECLINED: 323,
    EDITING: 324,
    UN_PAID: 325,
  };

  const { t } = useI18n();

  const statusTranslated = {
    DRAFT: t("draft"),
    PENDING: t("pending"),
    NEW: t("new"),
    IN_PROGRESS: t("in progress"),
    BEING_SHIPPED: t("being shipped"),
    CANCELED: t("canceled"),
    READY: t("ready"),
    DELIVERED: t("delivered"),
    DONE: t("done"),
    LOCKED: t("locked"),
    ARCHIVED: t("archived"),
    BLOCKED: t("blocked"),
    MAILING: t("mailing"),
    MAILED: t("mailed"),
    PROCESSING: t("processing"),
    EXPIRING: t("expiring"),
    EXPIRED: t("expired"),
    EDITABLE: t("editable"),
    IN_PRODUCTION: t("in production"),
    REJECTED: t("rejected"),
    ACCEPTED: t("accepted"),
    FAILED: t("failed"),
    WAITING_FOR_RESPONSE: t("waiting for response"),
    DECLINED: t("declined"),
    EDITING: t("editing"),
    UN_PAID: t("unpaid"),
  };

  const iconMap = {
    DRAFT: "pencil-square",
    PENDING: "hourglass-start",
    NEW: "sparkles",
    IN_PROGRESS: "arrow-progress",
    BEING_SHIPPED: "truck-fast",
    CANCELED: "file-slash",
    READY: "conveyor-belt",
    DELIVERED: "person-carry-box",
    DONE: "file-check",
    LOCKED: "lock",
    ARCHIVED: "archive",
    BLOCKED: "do-not-enter",
    MAILING: "envelope",
    MAILED: "inbox-out",
    PROCESSING: "timer",
    EXPIRING: "wind-warning",
    EXPIRED: "circle-exclamation",
    EDITABLE: "pen",
    IN_PRODUCTION: "conveyor-belt-arm",
    REJECTED: "xmark-to-slot",
    ACCEPTED: "check-to-slot",
    FAILED: "file-exclamation",
    WAITING_FOR_RESPONSE: "envelope",
    DECLINED: "ban",
    EDITING: "pen-to-square",
    UN_PAID: "money-bill-wave",
  };

  const getStatusIcon = (status) => {
    return iconMap[status] || "";
  };

  /**
   * Do NOT use this map for dynamic tailwind classes.
   * Always use static tailwind classes for best performance.
   * This map is just for dev reference.
   * See: https://tailwindcss.com/docs/content-configuration#dynamic-class-names
   **/
  const statusColorMap = {
    300: "cyan",
    301: "amber",
    302: "blue",
    303: "yellow",
    304: "green",
    305: "red",
    306: "lime",
    307: "emerald",
    308: "emerald",
    309: "gray",
    310: "gray",
    311: "orange",
    312: "indigo",
    313: "violet",
    314: "purple",
    315: "fuchsia",
    316: "pink",
    317: "cyan",
    318: "amber",
    319: "rose",
    320: "green",
    321: "red",
    322: "gray",
    323: "gray",
    324: "purple",
  };

  const statusClassesMap = {
    300: "text-cyan-500 bg-cyan-100 dark:bg-cyan-900",
    301: "text-amber-500 bg-amber-100 dark:bg-amber-900",
    302: "text-blue-500 bg-blue-100 dark:bg-blue-900",
    303: "text-yellow-500 bg-yellow-100 dark:bg-yellow-900",
    304: "text-green-500 bg-green-100 dark:bg-green-900",
    305: "text-red-500 bg-red-100 dark:bg-red-900",
    306: "text-lime-500 bg-lime-100 dark:bg-lime-900",
    307: "text-emerald-500 bg-emerald-100 dark:bg-emerald-900",
    308: "text-emerald-500 bg-emerald-100 dark:bg-emerald-900",
    309: "text-gray-600 bg-gray-200 dark:bg-gray-900",
    310: "text-gray-600 bg-gray-200 dark:bg-gray-900",
    311: "text-orange-500 bg-orange-100 dark:bg-orange-900",
    312: "text-indigo-500 bg-indigo-100 dark:bg-indigo-900",
    313: "text-violet-500 bg-violet-100 dark:bg-violet-900",
    314: "text-purple-500 bg-purple-100 dark:bg-purple-900",
    315: "text-fuchsia-500 bg-fuchsia-100 dark:bg-fuchsia-900",
    316: "text-pink-500 bg-pink-100 dark:bg-pink-500",
    317: "text-cyan-500 bg-cyan-100 dark:bg-cyan-900",
    318: "text-amber-500 bg-amber-100 dark:bg-amber-900",
    319: "text-rose-500 bg-rose-100 dark:bg-rose-900",
    320: "text-green-500 bg-green-100 dark:bg-green-900",
    321: "text-red-500 bg-red-100 dark:bg-red-900",
    322: "text-gray-600 bg-gray-200 dark:bg-gray-900",
    323: "text-gray-600 bg-gray-200 dark:bg-gray-900",
    324: "text-purple-500 bg-purple-100 dark:bg-purple-900",
    325: "text-amber-500 bg-amber-100 dark:bg-amber-900",
  };

  const statusHoverClassesMap = {
    300: "hover:text-cyan-600 hover:bg-cyan-200 dark:hover:bg-cyan-800 dark:hover:text-cyan-200",
    301: "hover:text-amber-600 hover:bg-amber-200 dark:hover:bg-amber-800 dark:hover:text-amber-200",
    302: "hover:text-blue-600 hover:bg-blue-200 dark:hover:bg-blue-800 dark:hover:text-blue-200",
    303: "hover:text-yellow-600 hover:bg-yellow-200 dark:hover:bg-yellow-800 dark:hover:text-yellow-200",
    304: "hover:text-green-600 hover:bg-green-200 dark:hover:bg-green-800 dark:hover:text-green-200",
    305: "hover:text-red-600 hover:bg-red-200 dark:hover:bg-red-800 dark:hover:text-red-200",
    306: "hover:text-lime-600 hover:bg-lime-200 dark:hover:bg-lime-800 dark:hover:text-lime-200",
    307: "hover:text-emerald-600 hover:bg-emerald-200 dark:hover:bg-emerald-800 dark:hover:text-emerald-200",
    308: "hover:text-emerald-600 hover:bg-emerald-200 dark:hover:bg-emerald-800 dark:hover:text-emerald-200",
    309: "hover:text-gray-700 hover:bg-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200",
    310: "hover:text-gray-700 hover:bg-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200",
    311: "hover:text-orange-600 hover:bg-orange-200 dark:hover:bg-orange-800 dark:hover:text-orange-200",
    312: "hover:text-indigo-600 hover:bg-indigo-200 dark:hover:bg-indigo-800 dark:hover:text-indigo-200",
    313: "hover:text-violet-600 hover:bg-violet-200 dark:hover:bg-violet-800 dark:hover:text-violet-200",
    314: "hover:text-purple-600 hover:bg-purple-200 dark:hover:bg-purple-800 dark:hover:text-purple-200",
    315: "hover:text-fuchsia-600 hover:bg-fuchsia-200 dark:hover:bg-fuchsia-800 dark:hover:text-fuchsia-200",
    316: "hover:text-pink-600 hover:bg-pink-200 dark:hover:bg-pink-800 dark:hover:text-pink-200",
    317: "hover:text-cyan-600 hover:bg-cyan-200 dark:hover:bg-cyan-800 dark:hover:text-cyan-200",
    318: "hover:text-amber-600 hover:bg-amber-200 dark:hover:bg-amber-800 dark:hover:text-amber-200",
    319: "hover:text-rose-600 hover:bg-rose-200 dark:hover:bg-rose-800 dark:hover:text-rose-200",
    320: "hover:text-green-600 hover:bg-green-200 dark:hover:bg-green-800 dark:hover:text-green-200",
    321: "hover:text-red-600 hover:bg-red-200 dark:hover:bg-red-800 dark:hover:text-red-200",
    322: "hover:text-gray-700 hover:bg-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200",
    323: "hover:text-gray-700 hover:bg-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200",
    324: "hover:text-purple-600 hover:bg-purple-200 dark:hover:bg-purple-800 dark:hover:text-purple-200",
    325: "hover:text-amber-600 hover:bg-amber-200 dark:hover:bg-amber-800 dark:hover:text-amber-200",
  };

  const statusClassesShadowMap = {
    300: "shadow-cyan-200/50 dark:shadow-cyan-900/50",
    301: "shadow-amber-200/50 dark:shadow-amber-900/50",
    302: "shadow-blue-200/50 dark:shadow-blue-900/50",
    303: "shadow-yellow-200/50 dark:shadow-yellow-900/50",
    304: "shadow-green-200/50 dark:shadow-green-900/50",
    305: "shadow-red-200/50 dark:shadow-red-900/50",
    306: "shadow-lime-200/50 dark:shadow-lime-900/50",
    307: "shadow-emerald-200/50 dark:shadow-emerald-900/50",
    308: "shadow-emerald-200/50 dark:shadow-emerald-900/50",
    309: "shadow-gray-300/50 dark:shadow-gray-900/50",
    310: "shadow-gray-300/50 dark:shadow-gray-900/50",
    311: "shadow-orange-200/50 dark:shadow-orange-900/50",
    312: "shadow-indigo-200/50 dark:shadow-indigo-900/50",
    313: "shadow-violet-200/50 dark:shadow-violet-900/50",
    314: "shadow-purple-200/50 dark:shadow-purple-900/50",
    315: "shadow-fuchsia-200/50 dark:shadow-fuchsia-900/50",
    316: "shadow-pink-200/50 dark:shadow-pink-900/50",
    317: "shadow-cyan-200/50 dark:shadow-cyan-900/50",
    318: "shadow-amber-200/50 dark:shadow-amber-900/50",
    319: "shadow-rose-200/50 dark:shadow-rose-900/50",
    320: "shadow-green-200/50 dark:shadow-green-900/50",
    321: "shadow-red-200/50 dark:shadow-red-900/50",
    322: "shadow-gray-300/50 dark:shadow-gray-900/50",
    323: "shadow-gray-300/50 dark:shadow-gray-900/50",
    324: "shadow-purple-200/50 dark:shadow-purple-900/50",
    325: "shadow-amber-200/50 dark:shadow-amber-900/50",
  };

  const chronologicalStatusList = [
    "DRAFT",
    "NEW",
    "IN_PROGRESS",
    "IN_PRODUCTION",
    "READY",
    "BEING_SHIPPED",
    "DELIVERED",
  ];

  const statusGroups = {
    EDITABLE: [
      "NEW",
      "WAITING_FOR_RESPONSE",
      "DECLINED",
      "REJECTED",
      "ACCEPTED",
      "MAILED",
      "IN_PROGRESS",
    ],
    SENDABLE: ["NEW", "MAILING", "MAILED", "EXPIRING", "EDITABLE"],
    ACCEPTABLE: [
      "NEW",
      "IN_PROGRESS",
      "IN_PRODUCTION",
      "READY",
      "BEING_SHIPPED",
      "WAITING_FOR_RESPONSE",
    ],
    CANCELABLE: ["NEW", "WAITING_FOR_RESPONSE"],
    EDITING: ["DRAFT", "EDITING"],
    CONVERTABLE: ["NEW", "WAITING_FOR_RESPONSE", "ACCEPTED"],
    NEXTABLE: ["NEW", "IN_PROGRESS", "IN_PRODUCTION", "READY", "BEING_SHIPPED"],
    IN_PROGRESS: ["IN_PROGRESS", "IN_PRODUCTION", "READY", "BEING_SHIPPED"],
    DONE: ["DELIVERED", "DONE"],
    ARCHIVABLE: ["DONE", "LOCKED"],
    EXTERNAL_EDITABLE: ["NEW", "MAILED", "REJECTED"],
    PRINTABLE: [
      "NEW",
      "WAITING_FOR_RESPONSE",
      "IN_PROGRESS",
      "IN_PRODUCTION",
      "READY",
      "BEING_SHIPPED",
      "DELIVERED",
    ],
  };

  const isSameStatus = (status1, status2) => {
    if (typeof status1 === "number") {
      status1 = getStatusName(status1);
    }
    if (typeof status2 === "number") {
      status2 = getStatusName(status2);
    }
    status1 = status1.toUpperCase();
    status2 = status2.toUpperCase();
    if (!statusMap[status1] || !statusMap[status2]) {
      throw new Error(`Status '${status1}' or '${status2}' not found`);
    }
    return status1 === status2;
  };

  const beautify = (status) => {
    if (typeof status === "number") {
      status = getStatusName(status);
    }
    return statusTranslated[status] || status;
  };

  const getNextStatus = (status) => {
    if (typeof status === "number") {
      status = getStatusName(status);
    }
    const statusIndex = chronologicalStatusList.indexOf(status);
    if (statusIndex === -1) {
      return false;
    }
    return chronologicalStatusList[statusIndex + 1];
  };

  const _isStatusIn = (code, labelList) => {
    const statusCodes = labelList.map((label) => statusMap[label]);
    return statusCodes.includes(code);
  };

  const getStatusName = (code) => {
    return Object.keys(statusMap).find((key) => statusMap[key] === code);
  };

  const isStatusInGroup = (status, groupName) => {
    const { addToast } = useToastStore();

    if (Array.isArray(groupName)) {
      for (const group of groupName) {
        if (_isStatusIn(status, statusGroups[group])) {
          return true;
        }
      }
      return false;
    }

    if (!statusGroups[groupName]) {
      addToast({
        type: "error",
        message: `Status group '${groupName}' not found`,
      });
      console.warn(`Status group '${groupName}' not found`);
      return false;
    }

    return _isStatusIn(status, statusGroups[groupName]);
  };

  const getStatusColor = (status) => {
    if (typeof status === "string") {
      status = statusMap[status];
    }
    return statusColorMap[status];
  };

  const getStatusShadow = (status) => {
    if (typeof status === "string") {
      status = statusMap[status];
    }
    return statusClassesShadowMap[status] || "";
  };

  const colorHexMap = {
    cyan: "#06b6d4",
    amber: "#f59e0b",
    blue: "#3b82f6",
    yellow: "#eab308",
    green: "#22c55e",
    red: "#ef4444",
    lime: "#84cc16",
    emerald: "#10b981",
    gray: "#9ca3af",
    orange: "#f97316",
    indigo: "#6366f1",
    violet: "#8b5cf6",
    purple: "#a855f7",
    fuchsia: "#d946ef",
    pink: "#ec4899",
    rose: "#f43f5e",
  };

  return {
    statusMap,
    statusTranslated,
    chronologicalStatusList,
    isStatusInGroup,
    getNextStatus,
    colorHexMap,
    beautify,
    getStatusName,
    getStatusColor,
    getStatusShadow,
    getStatusIcon,
    statusClassesMap,
    statusHoverClassesMap,
    statusClassesShadowMap,
    isSameStatus,
  };
};
