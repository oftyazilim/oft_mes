export default [
  {
    title: "Planlama",
    icon: { icon: "tabler-calendar-time" },
    action: "read",
    subject: ["planlama", "montaj"],
    children: [
      {
        title: "İş Emirleri - Montaj",
        to: "planlama-is-emirleri-montaj",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["planlama", "montaj"],
        // target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "planlama-ihtiyac-listesi",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["planlama", "montaj"],
        // target: '_blank',
      },
      {
        title: "Kapasite Hesapla",
        to: "planlama-kapasite-hesapla",
        icon: { icon: "tabler-list" },
        action: "create",
        subject: ["planlama"],
        // target: '_blank',
      },
    ],
  },
];
