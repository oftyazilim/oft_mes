export default [
  {
    title: "Planlama",
    icon: { icon: "tabler-calendar-time" },
    action: "read",
    subject: ["roles"],
    children: [
      {
        title: "İş Emirleri - Detay",
        to: "planlama-is-emirleri-montaj",
        icon: { icon: "tabler-list" },
        action: "read",
        subject: ["roles"],
        target: '_blank',
      },
    ],
  },
]
  