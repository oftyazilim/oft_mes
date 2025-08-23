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
            title: "Kullanıcılar",
            to: "personel-kullanicilar",
            action: "manage",
            subject: "all",
            // target: "_blank",
          },
          {
            title: "Yetki Yönetimi",
            to: { path: "/personel/yetki-yonetimi" },
            icon: { icon: "tabler-shield-lock" },
            action: "manage",
            subject: "users",
          },
          // {
          //   title: "Kullanıcı Listesi",
          //   to: { path: "/users" },
          // },
          // { title: "Roller", to: "apps-roles" },
          // { title: "İzinler", to: "apps-permissions" },
        ],
      },
    ],
  },
];
