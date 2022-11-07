export async function search(search, resourceName) {
  const {
    data: { resources },
  } = await Nova.request().get(`/nova-api/${resourceName}/search`, {
    params: {
      search: search,
      current: null,
      first: false,
      // withTrashed: true,
    },
  })

  return { resources }
}
