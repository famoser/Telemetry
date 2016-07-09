using System;
using System.Collections.Concurrent;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Navigation;
using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.FrameworkEssentials.Services;

namespace Famoser.Telemetry.UniversalWindows.Logger
{
    public class TelemetryLogger : ILogger
    {
        //meta data & runtime instances
        private const string TelemetryVersion = "1";
        private const string FileName = "28fd04a7-9b5c-4328-a342-3598f6b09419";
        private readonly Uri _url;
        private readonly string _applicationId;
        private readonly RestService _restService;

        //types
        private const string EventContentType = "event";
        private const string UserInfoContentType = "userInfo";
        private const string LogModelContentType = "logModel";

        //events
        private const string StartApplicationEvent = "startApplication";

        public TelemetryLogger(string url, string applicationId)
        {
            _url = new Uri(url);
            _applicationId = applicationId;
            _restService = new RestService(null, false);
            _posts.Enqueue(new[]
            {
                new KeyValuePair<string, string>("ContentType", EventContentType),
                new KeyValuePair<string, string>("Event", StartApplicationEvent)
            });
            PostWorker();
        }

        private Guid? _userGuid = null;
        private bool _instanceActive = false;
        private async void PostWorker()
        {
            lock (this)
            {
                if (_instanceActive)
                    return;
                _instanceActive = true;
            }

            if (!_userGuid.HasValue)
            {
                //try to get existing guid
                try
                {
                    StorageFile localFile = await ApplicationData.Current.LocalFolder.GetFileAsync(FileName);
                    if (localFile != null)
                        _userGuid = Guid.Parse(await FileIO.ReadTextAsync(localFile));
                }
                catch
                {
                    _userGuid = null;
                }

                if (_userGuid == null)
                {
                    //generate new guid
                    _userGuid = Guid.NewGuid();

                    //gather user info
                    var versionInfo = Windows.System.Profile.AnalyticsInfo.VersionInfo;
                    var deviceForm = Windows.System.Profile.AnalyticsInfo.DeviceForm;

                    //submit new user info
                    try
                    {
                        var response = await _restService.PostAsync(_url, new[]
                        {
                            new KeyValuePair<string, string>("Version", TelemetryVersion),
                            new KeyValuePair<string, string>("ApplicationId", _applicationId),
                            new KeyValuePair<string, string>("UserId", _userGuid.ToString()),
                            new KeyValuePair<string, string>("ContentType", UserInfoContentType),
                            new KeyValuePair<string, string>("DeviceName",
                                versionInfo.DeviceFamily + " " + versionInfo.DeviceFamilyVersion + " (" + deviceForm +
                                ")"),
                            new KeyValuePair<string, string>("SystemName", ""),
                            new KeyValuePair<string, string>("MetaData", "")
                        });
                    }
                    catch (Exception ex)
                    {
                        //can't do shit here, retry later
                        return;
                    }

                    try
                    {
                        StorageFile localFile = await ApplicationData.Current.LocalFolder.CreateFileAsync(FileName, CreationCollisionOption.ReplaceExisting);
                        await FileIO.WriteTextAsync(localFile, _userGuid.ToString());
                    }
                    catch (Exception ex)
                    {
                        //try again later
                        return;
                    }
                }
            }

            KeyValuePair<string, string>[] pairs;
            while (_posts.TryDequeue(out pairs))
            {
                try
                {
                    Array.Resize(ref pairs, pairs.Length + 3);
                    pairs[pairs.Length - 3] = new KeyValuePair<string, string>("Version", TelemetryVersion);
                    pairs[pairs.Length - 2] = new KeyValuePair<string, string>("ApplicationId", _applicationId);
                    pairs[pairs.Length - 1] = new KeyValuePair<string, string>("UserId", _userGuid.ToString());
                    await _restService.PostAsync(_url, pairs);
                }
                catch (Exception ex)
                {
                    // we dont care, its only logging...
                }
            }

            _instanceActive = false;
        }

        public void SendEvent(string eve)
        {
            _posts.Enqueue(new[]
            {
                new KeyValuePair<string, string>("ContentType", EventContentType),
                new KeyValuePair<string, string>("Event", eve)
            });

            PostWorker();
        }

        private ConcurrentBag<LogModel> _logModels = new ConcurrentBag<LogModel>();
        private readonly ConcurrentQueue<KeyValuePair<string, string>[]> _posts = new ConcurrentQueue<KeyValuePair<string, string>[]>();
        public void AddLog(LogModel model)
        {
            _logModels.Add(model);
            _posts.Enqueue(new[]
            {
                new KeyValuePair<string, string>("ContentType", LogModelContentType),
                new KeyValuePair<string, string>("Message", model.Message),
                new KeyValuePair<string, string>("LogLevel", ((int)model.LogLevel).ToString()),
                new KeyValuePair<string, string>("Location", model.Location)
            });

            PostWorker();
        }

        public void ClearLogs()
        {
            _logModels = new ConcurrentBag<LogModel>();
        }

        public List<LogModel> GetLogs(bool clearAfterwards = true)
        {
            var logModels = _logModels;
            if (clearAfterwards)
                _logModels = new ConcurrentBag<LogModel>();
            var res = new List<LogModel>(logModels);
            return res;
        }
    }
}
