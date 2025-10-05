import planlama from "./planlama";

export default [
  {
    title: "Üretim",
    icon: { icon: "tabler-automation" },
    action: "read",
    subject: ["planlama", "montaj", "mekanik"],
    children: [
      {
        title: "Duruşlar (Montaj)",
        href: `${import.meta.env.BASE_URL}uretim/duruslar-montaj`,
        icon: { icon: "tabler-clock-cog" },
        action: "read",
        subject: ["planlama", "montaj"],
        target: "oft-montaj",
      },
      {
        title: "Duruşlar (Mekanik)",
        href: `${import.meta.env.BASE_URL}uretim/duruslar-mekanik`,
        icon: { icon: "tabler-clock-cog" },
        action: "read",
        subject: ["planlama", "mekanik"],
        target: "oft-rollform",
      },
    ],
  },
];
