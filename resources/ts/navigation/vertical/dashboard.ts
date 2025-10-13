export default [
  {
    title: "Dashboards",
    icon: { icon: "tabler-smart-home" },
    policyKey: "menu:dashboard",
    children: [
      {
        title: "Büküm Dashboard",
        to: "dashboards-dash-bukum",
        icon: { icon: "tabler-chart-pie-2" },
        policyKey: "menu:dashboard:dash-bukum",
      },
      {
        title: "Büküm Dashboard test",
        to: "dashboards-dash-bukum-bak",
        icon: { icon: "tabler-chart-pie-2" },
        policyKey: "menu:dashboard:dash-bukum-bak",
      },
      {
        title: "Analytics",
        to: "dashboards-analytics",
        icon: { icon: "tabler-chart-pie-2" },
        policyKey: "menu:dashboard:analytics",
      },
      {
        title: "CRM",
        to: "dashboards-crm",
        icon: { icon: "tabler-cube" },
        policyKey: "menu:dashboard:crm",
      },
      {
        title: "Ecommerce",
        to: "dashboards-ecommerce",
        icon: { icon: "tabler-shopping-cart" },
        policyKey: "menu:dashboard:ecommerce",
      },
      {
        title: "Academy",
        to: "dashboards-academy",
        icon: { icon: "tabler-book" },
        policyKey: "menu:dashboard:academy",
      },
      {
        title: "Logistics",
        to: "dashboards-logistics",
        icon: { icon: "tabler-truck" },
        policyKey: "menu:dashboard:logistics",
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
