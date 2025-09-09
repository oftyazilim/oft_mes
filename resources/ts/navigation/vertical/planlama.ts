export default [
  {
    title: "Planlama",
    icon: { icon: "tabler-calendar-time" },
    action: "read",
    subject: ["planlama", "mekanik", "montaj"],
    children: [
      {
        title: "İş Emirleri - Montaj",
        to: "planlama-is-emirleri-montaj",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["planlama", "montaj"],
        target: '_blank',
      },
      {
        title: "İş Emirleri - Mekanik",
        to: "planlama-is-emirleri-mekanik",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["planlama", "mekanik"],
        target: '_blank',
      },
      {
        title: "Malzeme İhtiyaç Listesi",
        to: "planlama-ihtiyac-listesi",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["planlama"],
        target: '_blank',
      },
      {
        title: "Kapasite Hesapla",
        to: "planlama-kapasite-hesapla",
        icon: { icon: "tabler-list" },
        action: "create",
        subject: ["planlama"],
        target: '_blank',
      },
    ],
  },
];
  