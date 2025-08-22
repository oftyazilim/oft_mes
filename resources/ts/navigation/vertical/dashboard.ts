export default [
  {
    title: "Dashboards",
    icon: { icon: "tabler-smart-home" },
    children: [
      {
        title: "Analytics",
        to: "dashboards-analytics",
        icon: { icon: "tabler-chart-pie-2" },
        action: ["manage", "read"],
        subject: ["genel", "montaj", "all"],
      },
      {
        title: "CRM",
        to: "dashboards-crm",
        icon: { icon: "tabler-cube" },
        action: ["manage", "read"],
        subject: ["genel", "montaj", "all"],
      },
      {
        title: "Ecommerce",
        to: "dashboards-ecommerce",
        icon: { icon: "tabler-shopping-cart" },
        action: ["manage", "read"],
        subject: ["genel", "montaj", "all"],
      },
      {
        title: "Academy",
        to: "dashboards-academy",
        icon: { icon: "tabler-book" },
      },
      {
        title: "Logistics",
        to: "dashboards-logistics",
        icon: { icon: "tabler-truck" },
        action: ["manage", "read"],
        subject: ["genel", "Montaj", "all"],
      },
      // {
      //   title: "Ana Sayfa",
      //   to: "home-page",
      //   icon: { icon: "tabler-home" },
      //   // action: ["manage", "read"],
      //   // subject: ["genel", "Montaj", "all"],
      // },
    ],
  },
];
