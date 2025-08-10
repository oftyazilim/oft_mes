export default [
  {
    title: "Sistem Yönetimi",
    icon: { icon: "tabler-settings" },
    children: [
      {
        title: "Kullanıcılar",
        icon: { icon: "tabler-users" },
        children: [
          {
            title: "Kullanıcı Listesi",
            to: { path: "/users" },
          },
          { title: "Roller", to: "apps-roles" },
          { title: "İzinler", to: "apps-permissions" },
        ],
      },
    ],
  },
];
